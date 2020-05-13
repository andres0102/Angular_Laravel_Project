<?php

namespace App\Http\Controllers\Associates;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Helpers\{Common, ClientHelper, IndividualHelper, SubmissionHelper, PolicyHelper, ActivityLogHelper};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\Client;
use App\Models\Individuals\Individual;

class AssociateLeadController extends Controller
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
                'data' => ClientHelper::index(null, $sales_associate, 'lead')
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
                    'message' => 'lead_exists'
                ]);
            }

            return response()->json([
                'error' => false,
                'data' => ClientHelper::create(request()->only(ClientHelper::fields()), $sales_associate, true)
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
    public function updateIndividual($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->is_associate && $sales_associate = $user->sales_associate) {
          if ($individual = Individual::firstUuid($uuid)) {

            // Update Client Individual Record
            ClientHelper::update(request()->only(ClientHelper::fields('individual')), $individual, 'individual');
            // Update Client Individual :: Contact
            ClientHelper::update(request()->only(ClientHelper::fields('individual_contact')), $individual, 'individual_contact');
            // Update Client Individual :: Address
            ClientHelper::update(request()->only(ClientHelper::fields('individual_address')), $individual, 'individual_address');

            return response()->json([
                'error' => false,
            ]);
          } else {
            return Common::reject(404, 'individual_not_found');
          }
        } else {
          return Common::reject(403, 'user_is_not_associate');
        }
    }

}
