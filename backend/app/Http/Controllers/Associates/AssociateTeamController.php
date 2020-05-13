<?php

namespace App\Http\Controllers\Associates;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Illuminate\Support\Facades\Storage;

use App\Helpers\{SubmissionHelper, ActivityLogHelper, SubmissionCaseHelper, ClientHelper, IndividualHelper, MediaHelper};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\{Client, LifeAssured};
use App\Models\LegacyFA\Products\{Product, ProductOption, Rider};
use App\Models\LegacyFA\Submissions\{Submission, SubmissionCase};
use App\Models\Selections\LegacyFA\{SelectSubmissionCategory, SelectPaymentMode, SelectProvider, SelectProductCategory, SelectSubmissionStatus};

class AssociateTeamController extends Controller
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesforceAssociates()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_teams_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            return response()->json([
                'error' => false,
                'data' => $sales_associate->active_sales_agents()
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubmissionReport($year = null, $month = null)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('associate_teams_view')) {
          if ($user->is_associate && $sales_associate = $user->sales_associate) {
            $earliest_submission_date = Submission::where('date_submission', '<>', null)->orderBy('date_submission')->first()->date_submission->endOfMonth();
            $latest_submission_date = Submission::where('date_submission', '<>', null)->orderByDesc('date_submission')->first()->date_submission->endOfMonth();

            if (!$year || !$month) {
              $date_start = $latest_submission_date->copy()->startOfMonth();
              $date_end = $latest_submission_date->copy()->endOfMonth();
            } else {
              $date_start = Carbon::parse($year.'-'.$month)->startOfMonth();
              $date_end = Carbon::parse($year.'-'.$month)->endOfMonth();
            }

            $rows = [];
            $associates = $sales_associate->active_sales_agents($date_start);

            foreach($associates as $assc) {
              array_push($rows, [
                'name' => $assc->name,
                'lfa_code' => $assc->lfa_code,
                'designation' => $assc->latest_designation->title,
                'cases_count' => $assc->submission_cases()->whereHas('submission', function ($query) use ($date_start, $date_end) {
                                      $query->where('status_slug', 'submitted')
                                            ->where('deleted_at', NULL)
                                            ->whereBetween('date_submission', [$date_start, $date_end]);
                                  })->count(),
                'ape' => $assc->submission_cases()->whereHas('submission', function ($query) use ($date_start, $date_end) {
                                      $query->where('status_slug', 'submitted')
                                            ->where('deleted_at', NULL)
                                            ->whereBetween('date_submission', [$date_start, $date_end]);
                                  })->sum('ape'),
                'cases_count_ytd' => $assc->submission_cases()->whereHas('submission', function ($query) use ($date_start, $date_end) {
                                      $query->where('status_slug', 'submitted')
                                            ->where('deleted_at', NULL)
                                            ->whereBetween('date_submission', [$date_start->copy()->startOfYear(), $date_end]);
                                  })->count(),
                'ape_ytd' => $assc->submission_cases()->whereHas('submission', function ($query) use ($date_start, $date_end) {
                                      $query->where('status_slug', 'submitted')
                                            ->where('deleted_at', NULL)
                                            ->whereBetween('date_submission', [$date_start->copy()->startOfYear(), $date_end]);
                                  })->sum('ape'),
              ]);
            }

            $rows = collect($rows)->sortByDesc('ape')->sortByDesc('ape_ytd')->values();

            $prev_month = $date_start->copy()->subMonths(1);
            $next_month = $date_start->copy()->addMonths(1);

            return response()->json([
                'error' => false,
                'data' => $rows->toArray(),
                'report' => [
                  'prev' => [
                    'year' => $prev_month->format('Y'),
                    'month' => $prev_month->format('m'),
                  ],
                  'next' => [
                    'year' => $next_month->format('Y'),
                    'month' => $next_month->format('m'),
                  ],
                  'date' => $date_start->format('F Y'),
                  'earliest' => ($earliest_submission_date == $date_end),
                  'latest' => ($latest_submission_date == $date_end),
                  'associates_count' => $associates->count(),
                  'cases_count' => $rows->sum('cases_count'),
                  'cases_count_ytd' => $rows->sum('cases_count_ytd'),
                  'total_ape' => $rows->sum('ape'),
                  'total_ape_ytd' => $rows->sum('ape_ytd'),
                ]
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





}
