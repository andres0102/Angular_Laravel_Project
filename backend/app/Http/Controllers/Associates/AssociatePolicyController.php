<?php

namespace App\Http\Controllers\Associates;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use App\Helpers\PolicyHelper;
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\ClientPolicy;

class AssociatePolicyController extends Controller
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
     * Get Associate's Client Policies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_policies_view')) {
          if ($user->is_associate && ($sales_associate = $user->sales_associate)) {
            return response()->json([
                'error' => false,
                'data' => PolicyHelper::index(null, $sales_associate)
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
        if ($user->can('associate_policies_view')) {
            if ($user->is_associate && ($sales_associate = $user->sales_associate) && ($policy = ClientPolicy::firstUuid($uuid))) {
                if ($sales_associate->is($policy->sales_associate)) {
                    return response()->json([
                        'error' => false,
                        'data' => PolicyHelper::index($policy)
                    ]);
                } else {
                    return Common::reject(401, 'unauthorized_user');
                }
            } else {
                return Common::reject(404, 'policy_not_found');
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
    public function transactions($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_policies_view')) {
            if ($user->is_associate && ($sales_associate = $user->sales_associate) && ($policy = ClientPolicy::firstUuid($uuid))) {
                if ($sales_associate->is($policy->sales_associate)) {
                    return response()->json([
                        'error' => false,
                        'data' => PolicyHelper::transactions($policy)
                    ]);
                } else {
                    return Common::reject(401, 'unauthorized_user');
                }
            } else {
                return Common::reject(404, 'policy_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }
}
