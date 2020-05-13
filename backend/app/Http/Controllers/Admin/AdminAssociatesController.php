<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Helpers\{AssociateHelper, ClientHelper, IndividualHelper, SubmissionHelper, PolicyHelper, ActivityLogHelper, UserHelper};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\Client;
use App\Models\Users\User;
use App\Models\Selections\LegacyFA\SelectDesignation;

class AdminAssociatesController extends Controller
{
    /**
     * Create a new AssociateController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware to check if user is logged in.
        $this->middleware('auth');
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_view')) {
          return response()->json([
              'error' => false,
              'data' => AssociateHelper::index()
          ]);
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LegacyFA\Associates\Associate  $associate
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_view')) {
            if ($associate = Associate::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => AssociateHelper::index($associate)
                ]);
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubmissions($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_view')) {
            if ($associate = Associate::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => SubmissionHelper::index(null, $associate)
                ]);
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }

    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPolicies($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_view')) {
            if ($associate = Associate::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => PolicyHelper::index(null, $associate)
                ]);
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }

    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClients($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_view')) {
            if ($associate = Associate::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ClientHelper::index(null, $associate)
                ]);
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Get full index of resources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityLogs($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_view')) {
            if ($associate = Associate::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ActivityLogHelper::index('associates', $associate)
                ]);
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Store into database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_create')) {
            // Check that associate email doesn't already exists
            if (request()->has('email') && User::where('email', request()->input('email'))->first()) {
                return response()->json([
                    'error' => true,
                    'message' => 'email_exists'
                ]);
            }

            // Check that associate lfa_sl_no doesn't already exists
            if (request()->has('lfa_sl_no') && Associate::where('lfa_sl_no', request()->input('lfa_sl_no'))->first()) {
                return response()->json([
                    'error' => true,
                    'message' => 'sl_exists'
                ]);
            }

            request()->validate([
                'full_name' => 'required',
                'email' => 'required|email|unique:lfa_users.users',
                'lfa_sl_no' => 'required|digits:4|unique:lfa_associates.associates',
                'designation_slug' => 'required|string|exists:lfa_selections._lfa_designations,slug',
                'rnf_status_slug' => 'required|exists:lfa_selections._lfa_associates_rnf_status,slug',
                'banding_lfa' => 'required',
                'rnf_no' => 'required',
                'unit' => 'required',
                'group' => 'required',
                'date_rnf_approval' => 'required|date'
            ]);

            $designation = SelectDesignation::where('slug', request()->input('designation_slug'))->first();

             // User does not exists, lets create a user
            // begin with validating user and individual field columns
            request()->validate(UserHelper::validations('associate', true));
            request()->validate(IndividualHelper::validations('associate', true));

            // Validate & create User Account record (lfa_users.users)
            $user_array = array_merge(request()->only(UserHelper::fields()), ['is_associate' => true]);
            $user_assc = UserHelper::create($user_array, false);

            // Individual may not exists, lets check and create an individual
            // Validate & create Individual record (lfa_individuals.individuals)
            $individual_array = request()->only(IndividualHelper::fields());
            $individual_array['job_title'] = $designation->title;
            $individual_array['company_name'] = env('CO_NAME');
            $individual_array['business_nature'] = 'Financial advisory services';
            $individual = IndividualHelper::create($individual_array);

            // Tag Individual record to User record
            $user_assc->individual()->associate($individual)->save();
            $user_assc->update(['designation_slug' => request()->input('designation_slug')]);

            // Assign default user role
            $user_assc->assignRole('sales-associate');

            // Validate & create Sales Associate record (lfa_associates.associates)
            request()->validate(AssociateHelper::validations('associate', true));
            $associate_array = request()->only(AssociateHelper::fields());

            // Update Associate Salesforce Data
            $associate = AssociateHelper::create($associate_array, $user_assc->uuid, 'associate');
            // LFA-Code
            $lfa_code = request()->input('unit')['code'] . request()->input('group')['code'] . '-' . request()->input('lfa_sl_no');
            // Get Reporting To
            switch($designation->salesforce_tier) {
                case 1:
                    // Reporting to == manager
                    $reporting_uuid = request()->input('unit')['owner_uuid'];
                    break;
                case 2:
                    // Reporting to == director
                    $reporting_uuid = request()->input('group')['owner_uuid'];
                    break;
                default:
                    $reporting_uuid = null;
            }
            // Create Movement Record
            $movement = AssociateHelper::create([
                'lfa_code' => $lfa_code,
                'date_start' => request()->input('date_rnf_approval'),
                'designation_slug' => request()->input('designation_slug'),
                'reporting_uuid' => $reporting_uuid,
            ], $associate->uuid, 'movements');
            // Create Banding Record
            $banding_lfa_rate = (float) request()->input('banding_lfa');
            switch ($banding_lfa_rate) {
                case 0.5: $banding_rank = 1; break;
                case 0.55: $banding_rank = 2; break;
                case 0.6: $banding_rank = 3; break;
                case 0.65: $banding_rank = 4; break;
                case 0.7: $banding_rank = 5; break;
                case 0.75: $banding_rank = 6; break;
                case 0.8: $banding_rank = 7; break;
                default: $banding_rank = 1;
            }
            $banding_lfa = AssociateHelper::create([
                'rate' => request()->input('banding_lfa'),
                'type' => 2017,
                'rank' => $banding_rank,
                'date_start' => request()->input('date_rnf_approval'),
            ], $associate->uuid, 'bandings_lfa');

            // Send email to user
            UserHelper::sendEmailToken($user_assc);

            return response()->json([
                'error' => false,
                'data' => AssociateHelper::index($associate)
            ]);
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Update record inside database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($uuid)) {
                // Update user data (if any)
                if (request()->hasAny(UserHelper::fields())) {
                    request()->validate(UserHelper::validations());
                    $old_user_data = $associate->user->fresh();
                    $associate->user->update(request()->only(UserHelper::fields()));
                    $associate->log($user, 'user_updated', 'Associate user record updated.', $old_user_data, $associate->user->fresh());
                }

                if (request()->hasAny(ClientHelper::fields('individual')) || request()->hasAny(ClientHelper::fields('individual_contact')) || request()->hasAny(ClientHelper::fields('individual_address'))) {
                    // Get Individual Record
                    $individual = $associate->user->individual;
                    // Update Individual Record
                    ClientHelper::update(request()->only(ClientHelper::fields('individual')), $individual, 'individual');
                    // Update Individual :: Contact
                    ClientHelper::update(request()->only(ClientHelper::fields('individual_contact')), $individual, 'individual_contact');
                    // Update Individual :: Address
                    ClientHelper::update(request()->only(ClientHelper::fields('individual_address')), $individual, 'individual_address');
                    //
                    $associate->log($user, 'individual_updated', 'Associate personal particulars updated.');
                }

                // Update associate data (if any)
                if (request()->hasAny(AssociateHelper::fields())) {
                    request()->validate(AssociateHelper::validations());
                    $old_associate_data = $associate->fresh();
                    $associate->update(request()->only(AssociateHelper::fields()));
                    $associate->log($user, 'salesforce_data_updated', 'Associate salesforce data updated.', $old_associate_data, $associate->fresh());
                }

                return response()->json([
                    'message' => 'associate_updated',
                    'error' => false,
                    'data' => AssociateHelper::index($associate->fresh())
                ]);
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LegacyFA\Associates\Associate  $associate
     * @return \Illuminate\Http\Response
     */
    public function destroy($associate_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_delete')) {
            if ($associate = Associate::firstUuid($associate_uuid)) {
                // Method to offboard associate
                $associate->log($user, 'associate_deleted', 'Associate record deleted.', $associate->fresh(), null);
                $associate->delete();
                return response()->json([
                    'message' => 'associate_deleted',
                    'error' => false
                ]);
            } else {
                return Common::reject(403, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
    /** ===================================================================================================
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendWelcomeEmail($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($uuid)) {
                $assc_user = $associate->user;
                $associate->log($user, 'sent_welcome_email', 'Sent welcome email to Associate.');
                return UserHelper::sendEmailToken($assc_user);
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }








    /** ===================================================================================================
     * @return \Illuminate\Http\JsonResponse
     */
    public function newProviderCodes($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($uuid)) {
                request()->validate([
                    'provider_slug' => 'required|exists:lfa_selections._lfa_providers,slug',
                    'code' => 'required',
                ]);

                return AssociateHelper::create([
                    'provider_slug' => request()->input('provider_slug'),
                    'code' => request()->input('code'),
                ], $associate->uuid, 'providers_codes');
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
    public function deleteProviderCodes($associate_uuid, $pc_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($associate_uuid)) {
                if ($pcode = $associate->providers_codes()->where('uuid', $pc_uuid)->first()) {
                    $associate->log($user, 'provider_code_untagged', 'Provider Code untagged.', $pcode);
                    $pcode->delete();
                    return response()->json([
                        'error' => false,
                        'data' => AssociateHelper::index($associate)
                    ]);
                } else {
                    return Common::reject(404, 'provider_code_not_found');
                }
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * @return \Illuminate\Http\JsonResponse
     */
    public function newMovements($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($uuid)) {
                request()->validate([
                    'date_start' => 'required|date',
                    'lfa_sl_no' => 'required|digits:4',
                    'designation_slug' => 'required|string|exists:lfa_selections._lfa_designations,slug',
                    'unit' => 'required',
                    'group' => 'required',
                ]);

                $designation = SelectDesignation::where('slug', request()->input('designation_slug'))->first();

                // LFA-Code
                $lfa_code = request()->input('unit')['code'] . request()->input('group')['code'] . '-' . request()->input('lfa_sl_no');

                // Get Reporting To
                switch($designation->salesforce_tier) {
                    case 1:
                        // Reporting to == manager
                        $reporting_uuid = request()->input('unit')['owner_uuid'];
                        break;
                    case 2:
                        // Reporting to == director
                        $reporting_uuid = request()->input('group')['owner_uuid'];
                        break;
                    default:
                        $reporting_uuid = null;
                }
                // Create Movement Record
                return AssociateHelper::create([
                    'lfa_code' => $lfa_code,
                    'date_start' => request()->input('date_start'),
                    'designation_slug' => request()->input('designation_slug'),
                    'reporting_uuid' => $reporting_uuid,
                ], $associate->uuid, 'movements');
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
    public function deleteMovements($associate_uuid, $movement_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($associate_uuid)) {
                if ($movement = $associate->movements()->where('uuid', $movement_uuid)->first()) {
                    $associate->log($user, 'movement_deleted', 'Movement record deleted.', $movement);
                    $movement->delete();
                    return response()->json([
                        'error' => false,
                        'data' => AssociateHelper::index($associate)
                    ]);
                } else {
                    return Common::reject(404, 'movement_not_found');
                }
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }




    /** ===================================================================================================
     * @return \Illuminate\Http\JsonResponse
     */
    public function newBandingsLFA($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($uuid)) {
                request()->validate([
                    'rate' => 'required',
                    'date_start' => 'required|date',
                ]);
                // Update user data (if any)
                // Create Banding Record
                $banding_lfa_rate = (float) request()->input('rate');
                switch ($banding_lfa_rate) {
                    case 0.5: $banding_rank = 1; break;
                    case 0.55: $banding_rank = 2; break;
                    case 0.6: $banding_rank = 3; break;
                    case 0.65: $banding_rank = 4; break;
                    case 0.7: $banding_rank = 5; break;
                    case 0.75: $banding_rank = 6; break;
                    case 0.8: $banding_rank = 7; break;
                    default: $banding_rank = 1;
                }

                return AssociateHelper::create([
                    'rate' => $banding_lfa_rate,
                    'banding_type' => 2017,
                    'rank' => $banding_rank,
                    'date_start' => request()->input('date_start'),
                ], $associate->uuid, 'bandings_lfa');
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
    public function deleteBandingsLFA($associate_uuid, $banding_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($associate_uuid)) {
                if ($banding = $associate->bandings_lfa()->where('uuid', $banding_uuid)->first()) {
                    $associate->log($user, 'banding_lfa_deleted', 'LFA Banding record deleted.', $banding);
                    $banding->delete();
                    return response()->json([
                        'error' => false,
                        'data' => AssociateHelper::index($associate)
                    ]);
                } else {
                    return Common::reject(404, 'banding_not_found');
                }
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }




    /** ===================================================================================================
     * @return \Illuminate\Http\JsonResponse
     */
    public function newBandingsGI($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($uuid)) {
                request()->validate([
                    'rank' => 'required',
                    'date_start' => 'required|date',
                ]);
                return AssociateHelper::create([
                    'rank' => request()->input('rank'),
                    'date_start' => request()->input('date_start'),
                ], $associate->uuid, 'bandings_gi');
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
    public function deleteBandingsGI($associate_uuid, $banding_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associates_mgmt_update')) {
            if ($associate = Associate::firstUuid($associate_uuid)) {
                if ($banding = $associate->bandings_gi()->where('uuid', $banding_uuid)->first()) {
                    $associate->log($user, 'banding_gi_deleted', 'GI Banding record deleted.', $banding);
                    $banding->delete();
                    return response()->json([
                        'error' => false,
                        'data' => AssociateHelper::index($associate)
                    ]);
                } else {
                    return Common::reject(404, 'banding_not_found');
                }
            } else {
                return Common::reject(404, 'associate_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
}
