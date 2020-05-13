<?php

namespace App\Http\Controllers\Authentication;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Helpers\{UserHelper, AssociateHelper};

use App\Mail\UserResetPasswordEmail;
use App\Models\Users\{User, UserResetPassword, UserEmailToken};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Payroll\PayrollComputation;
use App\Transformers\{UserTransformer, AssociateTransformer};
// use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware to check if user is logged in.
        $this->middleware('auth', ['except' => [
            'checkUsername',
            'checkEmailToken',
            'resendEmailToken',
            'emailVerifyLogin',

            'checkEmailExists',
            'sendResetEmail',
            'checkResetToken',
            'resetPassword',
            'dashboardSummary',

        //     'checkVerifyEmailToken',
        //     'verifyEmail',
        ]]);

        // check if email is verified.
        // $this->middleware('verified', ['except' => [
        //     'checkEmailExists',
        //     'sendResetEmail',
        //     'checkVerifyEmailToken',
        //     'verifyEmail',
        //     'checkResetToken',
        //     'resetPassword',
        //     'me',
        //     'logout'
        // ]]);
    }

    /** ===================================================================================================
     * Check is <username>@email belongs to a valid user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUsername()
    {
        // Validate the email for the given request.
        request()->validate(['username' => 'required']);

        return UserHelper::checkUsername(request()->input('username'));
    }

    /** ===================================================================================================
     * Check validity of email verification token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkEmailToken()
    {
        // Validate the email for the given request.
        request()->validate(['token' => 'required|exists:lfa_users.verify_email']);

        return UserHelper::checkEmailToken(request()->input('token'));
    }

    /** ===================================================================================================
     * Resend a new email verification token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendEmailToken()
    {
        // Validate the email for the given request.
        request()->validate(['token' => 'required|exists:lfa_users.verify_email']);

        return UserHelper::resendEmailToken(request()->input('token'));
    }

    /** ===================================================================================================
     * Verify email for user via token and update password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailVerifyLogin()
    {
        // Validate the email for the given request.
        request()->validate([
            'token' => 'required|exists:lfa_users.verify_email',
            'password' => 'required|confirmed',
            'display_name' => 'required|string'
        ]);

        // Retrieve password reset request
        $request = UserEmailToken::where('token', request()->input('token'))->first();
        if ($valid = $request->valid()) {
            $user = $request->user;
            $user->password = bcrypt(request()->input('password'));
            $user->email_verified_at = $user->freshTimestamp();
            $user->last_seen = $user->freshTimestamp();
            $user->save();

            $user->individual->update(['full_name' => request()->input('display_name')]);

            $request->delete();

            $user->log($user, 'verified_email', 'User has completed email verification.', null, null, 'users', $user->uuid, $user->freshTimestamp());

            // Get the currently authenticated user
            return response()->json([
                'error' => false,
                'code'  => 'email-verified-successful',
                'username' => Str::replaceFirst('@legacyfa-asia.com', '', $user->email)
            ]);
        } else {
            // token has expired...
            // return the requested email...
            return response()->json([
                'error' => true,
                'code' => 'token-expired',
                'username' => Str::replaceFirst('@legacyfa-asia.com', '', $request->user->email)
            ]);
        }
    }

    /** ===================================================================================================
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        // Get the currently authenticated user
        $user = auth()->user();
        $user->last_seen = $user->freshTimestamp();
        $user->save();

        if ($user->is_associate && $user->associate_uuid) {
          return response()->json([
              'error' => false,
              'data' => AssociateHelper::index($user->sales_associate)
          ]);
        } else {
          return response()->json([
              'error' => false,
              'data' => UserHelper::index($user)
          ]);
        }
    }


    /** ===================================================================================================
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetEmail()
    {
        // Validate the email for the given request.
        request()->validate(['username' => 'required']);
        if ($user = User::where('email', Str::finish(Str::slug(request()->input('username'), '_'), '@legacyfa-asia.com'))->first()) {
            $dt = Carbon::now();

            // Create a password reset link that will expires in xx minutes.
            $reset_token = hash_hmac('sha256', Str::random(40), env('JWT_SECRET'));
            // remove all password reset tokens...
            $user->password_resets()->delete();
            // create new password reset token...
            $user->password_resets()->create([
              'email' => $user->email,
              'token' => $reset_token,
            ]);

            // We will send the password reset link to this user.
            $data = [
              'name' => $user->name,
              'email' => $user->email,
              'reset_url' => env('FRONTEND_URL') .'auth/reset-password/' . $reset_token,
              'request_date' => $dt->toFormattedDateString(),
              'request_time' => $dt->toTimeString(),
              'support_email' => env('CO_SUPPORT_EMAIL'),
              'support_no' => env('CO_SUPPORT_NO')
            ];
            $e = new UserResetPasswordEmail($data);
            \Mail::to($data['email'])->queue($e);
        }

        return response()->json([
            'message' => 'Password reset instructions sent to: ' . Str::finish(Str::slug(request()->input('username'), '_'), '@legacyfa-asia.com')
        ]);
    }
























    /** ===================================================================================================
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // Get the currently authenticated user
        if ($user = auth()->user()) {
          $user->last_seen = $user->freshTimestamp();
          $user->save();
          // Pass true to force the token to be blacklisted "forever"
          $user->token()->revoke();
          return response()->json([
            'status' => 'successfully_logged_out',
            'error' => false
          ]);
        } else {
          // check if token is already revoked...
          return response()->json([
            'status' => 'already_logged_out',
            'error' => false
          ]);
        }
    }




    /** ===================================================================================================
     * Check validity of reset token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkResetToken()
    {
        // Validate the email for the given request.
        request()->validate(['token' => 'required|exists:lfa_users.password_resets']);

        // Retrieve password reset request
        $request = UserResetPassword::where('token', request()->input('token'))->first();
        if ($valid = $request->valid()) {
            // token is valid...
            return response()->json([
                'error' => false,
                'status'  => 'token-valid',
                'data' => [
                  'username' => Str::replaceFirst('@legacyfa-asia.com', '', $request->email),
                  'token' => request()->input('token'),
                ]
            ]);
        } else {
            // token has expired...
            // remove all password reset tokens...
            $request->user->password_resets()->delete();
            // return the requested email...
            return response()->json([
                'error' => true,
                'status' => 'token-expired',
                'data' => [
                  'username' => Str::replaceFirst('@legacyfa-asia.com', '', $request->user->email)
                ]
            ]);
        }
    }

    /** ===================================================================================================
     * Reset the password for user via reset token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword()
    {
        // Validate the email for the given request.
        request()->validate([
          'token' => 'required|exists:lfa_users.password_resets,token,email,' . Str::finish(Str::slug(request()->input('username'), '_'), '@legacyfa-asia.com'),
          'password' => 'required|confirmed'
        ]);

        // Retrieve password reset request
        $request = UserResetPassword::where('token', request()->input('token'))->first();
        if ($valid = $request->valid()) {
            $user = $request->user;
            $user->password = bcrypt(request()->input('password'));
            $user->last_seen = $user->freshTimestamp();
            $user->save();
            // remove all password reset tokens...
            $user->password_resets()->delete();
            // Get the currently authenticated user
            return response()->json([
              'error' => false,
              'status'  => 'password-reset-successful'
            ]);
        } else {
            // token has expired...
            // remove all password reset tokens...
            $request->user->password_resets()->delete();
            // return the requested email...
            return response()->json([
              'error' => true,
              'status' => 'token-expired'
            ]);
        }
    }













    /**
     * Create a user account & send verify email to create password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAccount()
    {
        // Check that current user has permissions to create account(s)


        // Validate the email for the given request.
        request()->validate(['email' => 'required|email']);
        $user = User::where('email', Str::finish(request()->input('email'), '@legacyfa-asia.com'))->first();

        if ($user) {
            $dt = Carbon::now();

            // Create a password reset link that will expires in xx minutes.
            $reset_token = hash_hmac('sha256', Str::random(40), env('JWT_SECRET'));
            $user->password_resets()->delete();
            $user->password_resets()->create(['token' => $reset_token]);

            // We will send the password reset link to this user.
            $data = [
              'name' => $user->display_name,
              'email' => request()->input('email'),
              'reset_url' => 'https://beta.legacyfa-asia.com:4200/auth/reset-password/' . $reset_token,
              'request_date' => $dt->toFormattedDateString(),
              'request_time' => $dt->toTimeString(),
              'support_email' => env('CO_SUPPORT_EMAIL'),
              'support_no' => env('CO_SUPPORT_NO')
            ];
            $e = new UserResetPasswordEmail($data);
            \Mail::to($data['email'])->queue($e);
        }

        return response()->json([
            'message' => 'Password reset instructions sent to: ' . request()->input('email')
        ]);
    }


    /**
     * Verify email for user via token and update password.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail()
    {
        // Validate the email for the given request.
        request()->validate([
            'token' => 'required|exists:legacy_users.verify_email',
            'password' => 'required|confirmed'
        ]);

        // Retrieve password reset request
        $request = VerifyEmail::where('token', request()->input('token'))->first();
        if ($valid = $request->valid()) {
            $user = $request->user;
            $user->password = bcrypt(request()->input('password'));
            $user->email_verified_at = $user->freshTimestamp();
            $user->last_seen = $user->freshTimestamp();
            $user->save();

            $request->delete();

            // Get the currently authenticated user
            return response()->json([
                'error' => false,
                'code'  => 'email-verified-successful',
                'username' => Str::replaceFirst('@legacyfa-asia.com', '', $user->email)
            ]);
        } else {
            // token has expired...
            // return the requested email...
            return response()->json([
                'error' => true,
                'code' => 'token-expired',
                'username' => Str::replaceFirst('@legacyfa-asia.com', '', $request->user->email)
            ]);
        }
    }

    /**
     * Check validity of email verification token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkVerifyEmailToken()
    {
        // Validate the email for the given request.
        request()->validate(['token' => 'required|exists:legacy_users.verify_email']);

        // Retrieve password reset request
        $request = VerifyEmail::where('token', request()->input('token'))->first();
        if ($valid = $request->valid()) {
            // token is valid...
            return response()->json([
                'error' => false,
                'code'  => 'token-valid'
            ]);
        } else {
            // token has expired...
            // return the requested email...
            return response()->json([
                'error' => true,
                'code' => 'verify-email-token-expired',
                'username' => Str::replaceFirst('@legacyfa-asia.com', '', $request->user->email)
            ]);
        }
    }





    /**
     * Register a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     *
    public function register()
    {
        $credentials = request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|string|max:255|unique:legacy_users.users',
            // 'password' => 'required|string|min:6|confirmed'
        ]);

        event(new Registered($user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            // 'password' => Hash::make($credentials['email'])
        ])));

        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            // 'password' => Hash::make($credentials['email'])
        ]);

        // $token = auth()->login($user);
        // return $this->respondWithToken($token);

        return response()->json([
            'message' => 'Successfully registered, please verify email.',
            'error' => false
        ]);
    }
    */

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     *
    public function login()
    {
        $credentials = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if the credentials are valid
        if (!auth()->guard('web')->attempt($credentials)) return response()->json(['message' => 'Invalid Credentials.', 'error' => true], 401);

        $user = User::whereEmail(request()->input('email'))->first();
        $token = $user->createToken('Personal Access Token');

        return $token;
        return $this->respondWithToken($token);
    }
    */

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     *
    public function refresh()
    {
        // Pass true as the first param to force the token to be blacklisted "forever".
        // The second parameter will reset the claims for the new token
        return $this->respondWithToken(auth()->refresh());
    }
    */

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     *
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token->accessToken,
            'token_type' => 'bearer',
            'created_at' => $token->token->created_at,
            'expires_at' => $token->token->expires_at->toDateTimeString(),
            'error' => false
        ]);
    }
    */
}
