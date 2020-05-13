<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Mails\User\UserVerifyEmail;
use App\Models\Users\{User, UserEmailToken};
use App\Transformers\{Data_UserTransformer, UserShortTransformer};

class UserHelper
{
    /** ===================================================================================================
    * Query and return User record with joined fields
    **/
    public static function index($user = null)
    {
        // Individual Data
        $individual_contact_query = "(SELECT JSON_OBJECT(
                            'home_no', home_no,
                            'mobile_no', mobile_no,
                            'fax_no', fax_no,
                            'email', email
                        ) FROM lfa_individuals.contacts WHERE contacts.individual_uuid = uuid AND contact_type_slug = 'default')";
        $individual_address_query = "(SELECT JSON_OBJECT(
                            'block', block,
                            'street', street,
                            'unit', unit,
                            'building', building,
                            'city', city,
                            'postal', postal,
                            'country_slug', country_slug
                        ) FROM lfa_individuals.addresses WHERE addresses.individual_uuid = uuid AND address_type_slug = 'residential')";
        $individual_query = "(SELECT JSON_OBJECT(
                            'salutation_slug', salutation_slug,
                            'full_name', full_name,
                            'alias', alias,
                            'chinese_name', chinese_name,
                            'nric_no', nric_no,
                            'fin_no', fin_no,
                            'passport_no', passport_no,
                            'gender_slug', gender_slug,
                            'date_birth', date_birth,
                            'race_slug', race_slug,
                            'country_birth_slug', country_birth_slug,
                            'nationality_slug', nationality_slug,
                            'residency_status_slug', residency_status_slug,
                            'marital_status_slug', marital_status_slug,
                            'employment_status_slug', employment_status_slug,
                            'job_title', job_title,
                            'company_name', company_name,
                            'business_nature', business_nature,
                            'education_level_slug', education_level_slug,
                            'education_institution', education_institution,
                            'field_of_study', field_of_study,
                            'income_range', income_range,
                            'smoker', smoker,
                            'selected', selected,
                            'pdpa', pdpa,
                            'contact_information', " . $individual_contact_query . ",
                            'address_information', " . $individual_address_query . "
                        ) FROM lfa_individuals.individuals WHERE uuid = user.individual_uuid) as individual";

        // Begin to merge DB tables
        $query = DB::connection('lfa_users')
                    ->table('users as user')
                    ->where('user.deleted_at', null)
                    ->leftJoin('lfa_individuals.individuals as spouse', 'user.spouse_uuid', '=', 'spouse.uuid')
                    ->leftJoin('lfa_users.users as spouse_user', 'spouse.uuid', '=', 'spouse_user.individual_uuid')
                    ->leftJoin('lfa_associates.associates as spouse_assoc', 'spouse_user.associate_uuid', '=', 'spouse_assoc.uuid')
                    ->leftJoin('lfa__default.media', function($join)
                        {
                            $join->on('user.id', '=', 'media.model_id');
                            $join->on('model_type','=',DB::raw("'user'"));
                            $join->on('collection_name','=',DB::raw("'display_photo'"));
                        })
                    ->select(
                        'user.*',
                        'user.uuid as user_uuid',
                        'user.email as lfa_email',
                        'user.printer_id as lfa_printer_id',
                        'user.did_no as lfa_did_no',
                        DB::raw('ifnull(media.id, null) as profile'),
                        // Individual Data
                        DB::raw($individual_query),
                        // Spouse Data
                        'spouse_assoc.uuid as spouse_associate_uuid',
                        'spouse.full_name as spouse_name',
                        'spouse.job_title as spouse_job_title',
                        'spouse.company_name as spouse_company_name',
                        // LFA Data
                        DB::raw('(SELECT title FROM lfa_selections._lfa_onboarding_status WHERE slug = user.onboarding_status_slug) as onboarding_status'),
                        DB::raw('(SELECT title FROM lfa_selections._lfa_designations WHERE slug = user.designation_slug) as designation'),
                        DB::raw('(SELECT salesforce_tier FROM lfa_selections._lfa_designations WHERE slug = user.designation_slug) as tier')
                    )->orderBy('name');

        if ($user) {
            $results = $query->where('user.uuid', $user->uuid)->first();
        } else {
            $results = $query->get()->toArray();
        }

        return fractal($results, new Data_UserTransformer())->toArray()['data'];
    }


    /** ===================================================================================================
     * Function to return validations for table fields
     *
     */
    public static function validations($type = null, $required = false)
    {
      $required_str = ($required) ? 'required|' : '';

      switch ($type) {
        default:
            return [
              'email' => $required_str . 'string|email|max:255|unique:lfa_users.users',
              'designation_slug' => 'nullable|string|exists:lfa_selections._lfa_designations,slug',
              'bio' => 'nullable',
              'onboarding_status_slug' => 'nullable|string|exists:lfa_selections._lfa_onboarding_status,slug',
              'individual_uuid' => 'nullable|uuid|exists:lfa_individuals.individuals,uuid',
              'spouse_uuid' => 'nullable|uuid|exists:lfa_individuals.individuals,uuid',
              'associate_uuid' => 'nullable|uuid|exists:lfa_associates.associates,uuid',
              'onboarded_by' => 'nullable|uuid|exists:lfa_users.users,uuid',
              'offboarded_by' => 'nullable|uuid|exists:lfa_users.users,uuid',
              'printer_id' => 'nullable|string',
              'did_no' => 'nullable|string',
              'date_lfa_application' => 'nullable|date',
              'date_ceo_interview' => 'nullable|date',
              'date_contract_start' => 'nullable|date',
              'date_onboarded' => 'nullable|date',
              'date_offboarded' => 'nullable|date',
              'date_resigned' => 'nullable|date',
              'date_last_day' => 'nullable|date',
              'activated' => 'boolean',
              'setup' => 'boolean',
              'private' => 'boolean',
              'is_associate' => 'boolean',
              'is_staff' => 'boolean',
              'is_assistant' => 'boolean',
              'is_candidate' => 'boolean',
              'is_client' => 'boolean',
              'is_guest' => 'boolean',
            ];
      }
    }


    /** ===================================================================================================
     * Function to return valid table fields
     *
     */
    public static function fields($type = null)
    {
      return collect(self::validations($type))->keys()->all();
    }


    /** ===================================================================================================
     * Function to create/register user
     *
     */
    public static function create($data, $notify = false)
    {
      if (!Common::validData($data, 'password')) {
        $data = array_merge($data, [
          'password' => hash_hmac('sha256', Str::random(40), env('APP_KEY')),
        ]);
      }

      $user = User::create($data);
      $user->log(auth()->user(), 'model_updated', 'Created new user record.', null, $user->fresh());

      if (Common::parseDate($data, 'date_lfa_last_day', 'd/m/Y')) {
        $user->activated = false;
        $user->save();
      }

      if ($notify) self::sendEmailToken($user);
      return $user;
    }

    //   if (Common::validData($array, 'name')) {
    //     $name = $array['name'];
    //   } else {
    //     $parts = explode("@", $array['email']);
    //     $name = $parts[0];
    //   }


    /** ===================================================================================================
     * Function to send email token to user
     *
     */
    public static function sendEmailToken(User $user)
    {
        // Send user an email to notify account creation and verify email address.
        $dt = Carbon::now();

        // Create a password reset link that will expires in xx minutes.
        $reset_token = hash_hmac('sha256', Str::random(40), env('APP_KEY'));

        // Remove all instances of tokens..
        $user->verify_email()->delete();

        // Create a new token record..
        $user->verify_email()->create([
          'email' => $user->email,
          'token' => $reset_token
        ]);

        // We will send the password reset link to this user.
        $data = [
          'name' => $user->name,
          'email' => $user->email,
          'verify_url' => env('FRONTEND_URL') . 'auth/welcome/' . $reset_token,
          'request_date' => $dt->toFormattedDateString(),
          'request_time' => $dt->toTimeString(),
          'support_email' => env('CO_SUPPORT_EMAIL'),
          'support_no' => env('CO_SUPPORT_NO')
        ];
        $e = new UserVerifyEmail($data);
        \Mail::to($user->email)->queue($e);
        return response()->json([
            'error' => false,
            'status' => 'email-sent',
            'data' => [
              'email' => $user->email
            ],
        ]);
    }


    /** ===================================================================================================
     * Function to check validity of email token
     *
     */
    public static function checkEmailToken($token)
    {
        // Retrieve email verification request
        $request = UserEmailToken::where('token', $token)->first();
        if ($valid = $request->valid()) {
            // Email token is valid...
            return response()->json([
                'error' => false,
                'status'  => 'token-valid',
                'data'  => fractal($request->user, new UserShortTransformer())->toArray()['data'],
            ]);
        } else {
            // token has expired...
            // return the requested email...
            return response()->json([
                'error' => true,
                'status' => 'token-expired'
            ]);
        }
    }


    /** ===================================================================================================
     * Function to resend email token
     *
     */
    public static function resendEmailToken($token)
    {
        // Retrieve email verification request
        $request = UserEmailToken::where('token', $token)->first();
        $user = $request->user;
        return UserHelper::sendEmailToken($user);
    }


    /** ===================================================================================================
     * Function to deactivate user
     *
     */
    public static function deactivate(User $user)
    {
        return $user->update(['activated' => false]);
    }


    /** ===================================================================================================
     * Function to check existence of username
     *
     */
    public static function checkUsername($username)
    {
        $username = request()->input('username');
        $email = Str::finish($username, '@legacyfa-asia.com');

        if ($user = User::where('email', $email)->first()) {
          // return fractal($user, new UserTransformer())->respond();
          return response()->json([
            'data' => fractal($user, new UserShortTransformer())->toArray()['data'],
            'status' => 'user_exists',
            'error' => false
          ]);
        } else {
          return response()->json([
            'data' => [
              'email' => $email,
            ],
            'status' => 'user_not_found',
            'code' => 1000,
            'error' => true
          ]);
        }
    }
}