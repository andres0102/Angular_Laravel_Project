<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Helpers\Common;
use App\Models\Users\User;
use App\Models\LegacyFA\Associates\Associate;
use App\Helpers\{UserHelper, IndividualHelper};

class AdminUsersController extends Controller
{
    /**
     * Create a new AdminUsersController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware to check if user is logged in.
        $this->middleware('auth');
    }



    /**
     * Display a listing of the resource.
     * Return all users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('users_mgmt_view')) {
            return response()->json([
                'error' => false,
                'data' => UserHelper::index()
            ]);
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('users_mgmt_create')) {
            // Lets create a user, begin with validating user and individual field columns
            request()->validate(UserHelper::validations('user', true));
            request()->validate(IndividualHelper::validations('individual', true));

            // Validate & create User Account record (lfa_users.users)
            $user_array = request()->only(UserHelper::fields());
            $new_user = UserHelper::create($user_array, false);

            // Individual may not exists, lets check and create an individual
            // Validate & create Individual record (lfa_individuals.individuals)
            $individual_array = request()->only(IndividualHelper::fields());
            $individual = IndividualHelper::create($individual_array);

            // Tag Individual record to User record
            $new_user->individual()->associate($individual)->save();

            if ($associate = $new_user->sales_associate) {
                // Assign default user role
                $new_user->is_associate = true;
                $new_user->activated = $associate->active;
                $new_user->save();
                $new_user->assignRole('sales-associate');
            }

            return response()->json([
                'message' => 'user_created',
                'error' => false,
                'data' => UserHelper::index($new_user->fresh())
            ]);
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Users\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('users_mgmt_view')) {
            if ($selected_user = User::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => UserHelper::index($selected_user)
                ]);
            } else {
                return Common::reject(404, 'user_not_found');
            }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Users\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('users_mgmt_update')) {
            if ($update_user = User::firstUuid($user_uuid)) {
                // Update user data (if any)
                if (request()->hasAny(UserHelper::fields())) {
                    request()->validate(UserHelper::validations());
                    $old_user_data = $update_user->fresh();
                    $update_user->update(request()->only(UserHelper::fields()));
                    $update_user->log($user, 'user_updated', 'User record updated.', $old_user_data, $update_user->fresh());
                }

                // Update individual data (if any)
                if (request()->hasAny(IndividualHelper::fields())) {
                    request()->validate(IndividualHelper::validations());
                    $old_individual_data = $update_user->individual->fresh();
                    $update_user->individual->update(request()->only(IndividualHelper::fields()));
                    $update_user->log($user, 'user_updated', 'User personal record updated.', $old_individual_data, $update_user->individual->fresh());
                }

                // Update individual contact data (if any)
                if (request()->hasAny(IndividualHelper::fields('contacts'))) {
                    request()->validate(IndividualHelper::validations('contacts'));
                    $old_contact_data = $update_user->individual->contacts->where('contact_type_slug', 'default')->fresh();
                    $update_user->individual->contacts()->updateOrCreate(['contact_type_slug' => 'default'], request()->only(IndividualHelper::fields('contacts')));
                    $update_user->log($user, 'user_updated', 'User default contact information updated.', $old_contact_data, $update_user->individual->contacts->where('contact_type_slug', 'default')->fresh());
                }

                // Update individual address data (if any)
                if (request()->hasAny(IndividualHelper::fields('addresses'))) {
                    request()->validate(IndividualHelper::validations('addresses'));
                    $old_address_data = $update_user->individual->addresses->where('address_type_slug', 'residential')->fresh();
                    $update_user->individual->addresses()->updateOrCreate(['address_type_slug' => 'residential'], request()->only(IndividualHelper::fields('addresses')));
                    $update_user->log($user, 'user_updated', 'User residential address information updated.', $old_address_data, $update_user->individual->addresses->where('address_type_slug', 'residential')->fresh());
                }

                return response()->json([
                    'message' => 'user_updated',
                    'error' => false,
                    'data' => UserHelper::index($update_user->fresh())
                ]);
            } else {
                return Common::reject(404, 'user_not_found');
            }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Users\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('users_mgmt_delete')) {
            if ($selected_user = User::firstUuid($uuid)) {
                $selected_user->log($user, 'user_deleted', 'User record deleted.', $selected_user->fresh(), null);
                $selected_user->activated = false;
                $selected_user->save();
                $selected_user->delete();
                return response()->json([
                    'message' => 'user_deleted',
                    'error' => false
                ]);
            } else {
                return Common::reject(404, 'user_not_found');
            }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }
}
