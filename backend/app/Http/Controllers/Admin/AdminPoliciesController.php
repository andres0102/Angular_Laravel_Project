<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Helpers\{Common, ClientHelper, IndividualHelper, SubmissionHelper, PolicyHelper, ActivityLogHelper};
use App\Models\LegacyFA\Clients\Client;

class AdminPoliciesController extends Controller
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
        if ($user->can('clients_policies_mgmt_view')) {
          return response()->json([
            'error' => false,
            'data' => PolicyHelper::index()
          ]);
        } else {
            // Forbidden HTTP Request
            return Common::reject(401, 'unauthorized_user');
        }
    }
}
