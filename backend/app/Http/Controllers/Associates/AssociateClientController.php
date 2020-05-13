<?php

namespace App\Http\Controllers\Associates;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Helpers\{Common, ClientHelper, IndividualHelper, SubmissionHelper, PolicyHelper, ActivityLogHelper};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\Client;

class AssociateClientController extends Controller
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
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            return response()->json([
                'error' => false,
                'data' => ClientHelper::index(null, $sales_associate, 'client')
            ]);
          } else {
            // Forbidden HTTP Request
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }
    public function full_index()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            return response()->json([
                'error' => false,
                'data' => ClientHelper::index(null, $sales_associate)
            ]);
          } else {
            // Forbidden HTTP Request
            return Common::reject(403, 'user_is_not_associate');
          }
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
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($client = Client::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => SubmissionHelper::index(null, null, $client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
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
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($client = Client::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => PolicyHelper::index(null, null, $client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
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
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($client = Client::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ActivityLogHelper::index('clients', $client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    public function getLifeAssured($uuid) {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($client = Client::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ClientHelper::life_assured($client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    public function getNominees($uuid) {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($client = Client::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ClientHelper::index($client, null, 'nominee')
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
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
        if ($user->can('associate_clients_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($client = Client::firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ClientHelper::index($client)
                ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
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
        if ($user->can('associate_clients_create')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            request()->validate(ClientHelper::validations(null, true));

            if (request()->input('new') !== true && $sales_associate->findClient(strtoupper(request()->input('display_name')), strtoupper(request()->input('nric_no')))) {
                return response()->json([
                    'error' => true,
                    'message' => 'client_exists'
                ]);
            }

            return response()->json([
                'error' => false,
                'data' => ClientHelper::create(request()->only(ClientHelper::fields()), $sales_associate)
            ]);
          } else {
            // Forbidden HTTP Request
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
            // Forbidden HTTP Request
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
        if ($user->can('associate_clients_update')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            if ($client = Client::firstUuid($uuid)) {
              $old_data = $client;

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

              $client->log($user, 'client_updated', 'Client record updated.', $old_data, $client->fresh(), 'clients', $client->uuid, $client->freshTimestamp());

              return response()->json([
                  'error' => false,
                  'data' => ClientHelper::index($client)
              ]);
            } else {
              return Common::reject(404, 'client_not_found');
            }
          } else {
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }


    /** ===================================================================================================
     * Merge clients.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mergeClients()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_clients_update')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            request()->validate([
              'from_client_uuid' => 'required|uuid|exists:lfa_clients.clients,uuid',
              'to_client_uuid' => 'required|uuid|exists:lfa_clients.clients,uuid',
            ]);

            $from_client = Client::where('associate_uuid', $sales_associate->uuid)->firstUuid(request()->input('from_client_uuid'));
            $to_client = Client::where('associate_uuid', $sales_associate->uuid)->firstUuid(request()->input('to_client_uuid'));

            // Merge Client
            if ($from_client && $to_client) {
              $to_client->log($user, 'client_merged', 'Client records merged.', $from_client, null, 'clients', $to_client->uuid);
              $from_client->mergeTo($to_client);
              return response()->json([
                  'error' => false,
                  'data' => $to_client->fresh()
              ]);
            } else {
              // Clients not found
              return Common::reject(404, 'clients_not_found');
            }
          } else {
            // Forbidden HTTP Request
            return Common::reject(403, 'user_is_not_associate');
          }
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }
}
