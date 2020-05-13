<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Helpers\{Common, ClientHelper, IndividualHelper, SubmissionHelper, PolicyHelper, ActivityLogHelper};
use App\Models\LegacyFA\Clients\Client;

class AdminClientsController extends Controller
{
    /**
     * Create a new AdminClientsController instance.
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
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('clients_mgmt_view')) {
            return response()->json([
                'error' => false,
                'data' => ClientHelper::index(null, null, 'client', true)
            ]);
        } else {
            // Forbidden HTTP Request
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
        if ($user->can('clients_mgmt_view')) {
            if ($client = Client::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => SubmissionHelper::index(null, null, $client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
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
        if ($user->can('clients_mgmt_view')) {
            if ($client = Client::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => PolicyHelper::index(null, null, $client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
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
        if ($user->can('clients_mgmt_view')) {
            if ($client = Client::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ActivityLogHelper::index('clients', $client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    public function getLifeAssured($uuid) {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('clients_mgmt_view')) {
            if ($client = Client::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ClientHelper::life_assured($client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LegacyFA\Clients\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($client_uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('clients_mgmt_view')) {
            if ($client = Client::withTrashed()->firstUuid($client_uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ClientHelper::index($client)
                ]);
            } else {
                return Common::reject(404, 'client_not_found');
            }
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
        if ($user->can('clients_mgmt_update')) {
            if ($client = Client::withTrashed()->firstUuid($uuid)) {
              $old_data = $client->fresh();
              // Update Client Display Name
              ClientHelper::update(request()->only(ClientHelper::fields('display_name')), $client, 'display_name');
              // Update Client Record
              ClientHelper::update(request()->only(ClientHelper::fields('client')), $client, 'client');

              $client_type_slug = request()->input('client_type_slug') ?? $client->client_type_slug;
              if ($client_type_slug == 'individual') {
                if (!$individual = $client->individual) {
                  // Individual does not exists
                  // Lets create a new indivdual
                  $individual = IndividualHelper::create([
                    'full_name' => Common::trimStringUpper(request()->input('display_name') ?? request()->input('full_name') ?? null),
                    'nric_no' => Common::trimStringUpper(request()->input('nric_no') ?? null),
                  ]);
                }

                // Update Client Individual Record
                ClientHelper::update(request()->only(ClientHelper::fields('individual')), $individual, 'individual');
                // Update Client Individual :: Contact
                ClientHelper::update(request()->only(ClientHelper::fields('individual_contact')), $individual, 'individual_contact');
                // Update Client Individual :: Address
                ClientHelper::update(request()->only(ClientHelper::fields('individual_address')), $individual, 'individual_address');
              }

              $client->log($user, 'client_updated', 'Client record updated.', $old_data, $client->fresh());

              return response()->json([
                  'error' => false,
                  'data' => ClientHelper::index($client)
              ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }
}
