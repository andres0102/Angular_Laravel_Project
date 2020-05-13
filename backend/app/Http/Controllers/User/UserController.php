<?php

namespace App\Http\Controllers\User;

use App\Helpers\{Common, NoticeHelper};
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use App\Models\General\Notice;
use App\Models\Users\{User};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Payroll\{PayrollBatch, PayrollComputation};
use App\Transformers\{AssociateTransformer, AssociateDataShortTransformer, NoticeDataTransformer};

class UserController extends Controller
{
    /**
     * Create a new AssociateController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware to check if user is logged in.
        $this->middleware('auth', ['except' => [
          'dashboardNotices',
          // 'getNotice',
          'noticeGetComments',
          'dashboardLeaderboardAssociates'
        ]]);
    }


    /** ===================================================================================================
     * Get the General Notices.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardNotices()
    {
      $query = NoticeHelper::index()->toArray();
      $links = collect($query['meta']['pagination']['links']);

      return response()->json([
          'error' => false,
          'data' => $query['data'],
          'page_info' => [
            'total' => $query['meta']['pagination']['total'] ?? null,
            'count' => $query['meta']['pagination']['count'] ?? null,
            'per_page' => $query['meta']['pagination']['per_page'] ?? null,
            'current_page' => $query['meta']['pagination']['current_page'] ?? null,
            'total_pages' => $query['meta']['pagination']['total_pages'] ?? null,
          ],
          'urls' => [
            'prev_page_url' => ($links->isNotEmpty()) ? $links['previous'] ?? null : null,
            'next_page_url' => ($links->isNotEmpty()) ? $links['next'] ?? null : null,
          ],
      ]);
    }


    /** ===================================================================================================
     * Get the General Notices.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getNotice($uuid)
    // {
    //     // Check if authenticated user has permission to execute this action
    //     if ($user = auth()->user()) {
    //       if ($notice = Notice::firstUuid($uuid)) {

    //         return response()->json([
    //             'error' => false,
    //             'data' => NoticeHelper::index($notice)
    //         ]);
    //       } else {
    //           return Common::reject(404, 'notice_not_found');
    //       }
    //     } else {
    //         // Forbidden HTTP Request
    //         return Common::reject(401, 'unauthorized_user');
    //     }
    // }

    public function readNotice($uuid)
    {
      if ($user = auth()->user()) {
        if ($notice = Notice::firstUuid($uuid)) {
          $notice->log($user, 'read', 'Notice has been read by user.', null, null, 'notices', $notice->uuid, $notice->freshTimestamp());

          return response()->json([
              'error' => false,
              'data' => NoticeHelper::index($notice)
          ]);
        } else {
          return Common::reject(404, 'notice_not_found');
        }
      } else {
        // Forbidden HTTP Request
        return Common::reject(401, 'unauthorized_user');
      }
    }



    public function noticeGetComments($uuid)
    {
        if ($notice = Notice::firstUuid($uuid)) {
          $query = collect(NoticeHelper::comments($notice));
          $links = collect($query['meta']['pagination']['links']);

          return response()->json([
              'error' => false,
              'data' => $query['data'],
              'page_info' => [
                'total' => $query['meta']['pagination']['total'] ?? null,
                'count' => $query['meta']['pagination']['count'] ?? null,
                'per_page' => $query['meta']['pagination']['per_page'] ?? null,
                'current_page' => $query['meta']['pagination']['current_page'] ?? null,
                'total_pages' => $query['meta']['pagination']['total_pages'] ?? null,
              ],
              'urls' => [
                'prev_page_url' => ($links->isNotEmpty()) ? $links['previous'] ?? null : null,
                'next_page_url' => ($links->isNotEmpty()) ? $links['next'] ?? null : null,
              ],
          ]);
        } else {
            return Common::reject(404, 'notice_not_found');
        }
    }

    public function noticeAddComment($uuid)
    {
      if ($user = auth()->user()) {
        if ($notice = Notice::firstUuid($uuid)) {

          request()->validate(['content' => 'required']);
          $comment = $notice->comment(request()->input('content'));

          $notice->log($user, 'commented', 'User has commented on notice.', null, null, 'comments', $comment->uuid, $comment->freshTimestamp());

          return response()->json([
              'error' => false,
              'data' => NoticeHelper::comments($notice, $comment),
              'notice' => NoticeHelper::index($notice)
          ]);
        } else {
          return Common::reject(404, 'notice_not_found');
        }
      } else {
        // Forbidden HTTP Request
        return Common::reject(401, 'unauthorized_user');
      }
    }


    /** ===================================================================================================
     * Get the leaderboard (associates) for authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardLeaderboardAssociates()
    {
        // $year = request()->input('year') ?? Carbon::now()->year;
        $type = request()->input('type') ?? 'fyc';
        $year = request()->input('year') ?? null;
        $month = request()->input('month') ?? null;
        $range = request()->input('range') ?? 'all';
        $limit = request()->input('limit') ?? 10;

        switch($type) {
          case 'fyc': {
            // Rank by FYC
            $db_raw = DB::connection('lfa_payroll')
                     ->table('computations as payroll_comp')
                     ->leftJoin('lfa_users.users as assc_user', 'payroll_comp.payee_uuid', '=', 'assc_user.associate_uuid')
                     ->select(
                        'payroll_comp.payee_uuid as associate_uuid',
                        DB::raw('(SELECT full_name from lfa_individuals.individuals where individuals.uuid = assc_user.individual_uuid) as associate_name'),
                        DB::raw('(SELECT gender_slug from lfa_individuals.individuals where individuals.uuid = assc_user.individual_uuid) as associate_gender'),
                        DB::raw('(SELECT title from lfa_selections._lfa_designations where _lfa_designations.slug = assc_user.designation_slug) as associate_designation'),
                        DB::raw('coalesce(sum(amount),0) as fyc'))
                     ->where('commission_tier', 1)
                     ->where('commission_type', 'first-year')
                     ->where('firm_revenue', false)
                     ->whereIn('payroll_era', ['lfa', 'gi'])
                     ->groupBy(['associate_uuid', 'associate_name', 'associate_gender', 'associate_designation'])->orderByDesc('fyc')->limit($limit);

            switch ($range) {
              case 'month': {
                $latest_batch = PayrollBatch::orderByDesc('year')->orderByDesc('month')->first();
                if ($batch = PayrollBatch::where('year', $year)->where('month', $month)->first()) {
                  $selected_year = $batch->year;
                  $selected_month = $batch->month;
                } else if ($latest_batch) {
                  $selected_year = $latest_batch->year;
                  $selected_month = $latest_batch->month;
                } else {
                  // No payroll batch has been released yet in the selected range
                  return Common::reject(400, 'invalid_request');
                }

                $query = $db_raw->where('year', $selected_year)->where('month', $selected_month)->get();
                $query->map(function ($item) use ($selected_year, $selected_month) {
                  $associate = $item->associate_uuid;
                  $submissions = DB::connection('lfa_submissions')
                                    ->table('submissions')
                                    ->where('associate_uuid', $associate)
                                    ->where('deleted_at', NULL)
                                    ->where('status_slug', 'submitted')
                                    ->where('date_submission', 'like', $selected_year.'-'.$selected_month. '%')
                                    ->select('uuid')->get()->pluck('uuid');
                  $case_info = DB::connection('lfa_submissions')
                                   ->table('cases')
                                   ->select(DB::raw('(JSON_OBJECT("count", count(*),"ape", sum(ape))) as value'))
                                   ->where('associate_uuid', $associate)
                                   ->whereIn('submission_uuid', $submissions)
                                   ->get();
                  $decoded = json_decode($case_info->first()->value);
                  $item->ape = $decoded->ape;
                  $item->count = $decoded->count;
                });

                return response()->json([
                    'error' => false,
                    'data' => [
                      'range' => $range,
                      'year' => $selected_year,
                      'month' => Carbon::parse($selected_year . '-' . $selected_month)->format('F'),
                      'limit' => $limit,
                      'leaderboard' => fractal($query->toArray(), new AssociateDataShortTransformer())->toArray()['data']
                    ]
                ]);

                break;
              }
              case 'year': {
                $latest_batch = PayrollBatch::orderByDesc('year')->first();

                if ($batch = PayrollBatch::where('year', $year)->first()) {
                  $selected_year = $batch->year;
                } else if ($latest_batch = PayrollBatch::orderByDesc('year')->first()) {
                  $selected_year = $latest_batch->year;
                } else {
                  // No payroll batch has been released yet in the selected range
                  return Common::reject(400, 'invalid_request');
                }

                $query = $db_raw->where('year', $selected_year)->get();
                $query->map(function ($item) use ($selected_year) {
                  $associate = $item->associate_uuid;
                  $submissions = DB::connection('lfa_submissions')
                                    ->table('submissions')
                                    ->where('status_slug', 'submitted')
                                    ->where('deleted_at', NULL)
                                    ->where('associate_uuid', $associate)
                                    ->where('date_submission', 'like', $selected_year.'-%')
                                    ->select('uuid')->get()->pluck('uuid');
                  $case_info = DB::connection('lfa_submissions')
                                   ->table('cases')
                                   ->select(DB::raw('(JSON_OBJECT("count", count(*),"ape", sum(ape))) as value'))
                                   ->where('associate_uuid', $associate)
                                   ->whereIn('submission_uuid', $submissions)
                                   ->get();
                  $decoded = json_decode($case_info->first()->value);
                  $item->ape = $decoded->ape;
                  $item->count = $decoded->count;
                });

                return response()->json([
                    'error' => false,
                    'data' => [
                      'range' => $range,
                      'year' => $selected_year,
                      'month' => null,
                      'limit' => $limit,
                      'leaderboard' => fractal($query->toArray(), new AssociateDataShortTransformer())->toArray()['data']
                    ]
                ]);
                break;
              }
            }
            break;
          }
          case 'submission-ape': {
            // Rank by sunmission-ape
            $db_raw = DB::connection('lfa_submissions')
                     ->table('submissions as submission')
                     ->leftJoin('lfa_users.users as assc_user', 'submission.associate_uuid', '=', 'assc_user.associate_uuid')
                     ->select(
                        'submission.associate_uuid',
                        DB::raw('(SELECT full_name from lfa_individuals.individuals where individuals.uuid = assc_user.individual_uuid) as associate_name'),
                        DB::raw('(SELECT gender_slug from lfa_individuals.individuals where individuals.uuid = assc_user.individual_uuid) as associate_gender'),
                        DB::raw('(SELECT title from lfa_selections._lfa_designations where _lfa_designations.slug = assc_user.designation_slug) as associate_designation'),
                        DB::raw('coalesce(sum(amount),0) as fyc'))
                     ->where('commission_tier', 1)
                     ->where('commission_type', 'first-year')
                     ->where('firm_revenue', false)
                     ->whereIn('payroll_era', ['lfa', 'gi'])
                     ->groupBy(['associate_uuid', 'associate_name', 'associate_gender', 'associate_designation'])->orderByDesc('fyc')->limit($limit);

          }
        }

      return Common::reject(400, 'invalid_request');
    }



    /** ===================================================================================================
     * Get the Associate yearly payroll summary data for authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardSummaryAssociates()
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        $associate_uuid = request()->input('associate_uuid') ?? ($user->is_associate) ? $user->sales_associate->uuid : null;
        if (!$selected_associate = Associate::firstUuid($associate_uuid)) return Common::reject(404, 'associate_not_found');
        $is_default_associate = ($user->is_associate && $user->sales_associate && $user->sales_associate->is($selected_associate)) ?? false;

        if ($user->can('associate_dashboard') && $is_default_associate) {

          if ($is_default_associate) {
            $user->last_seen = $user->freshTimestamp();
            $user->save();
          }

          // Get the variables
          $year = request()->input('year') ?? null;
          $month = request()->input('month') ?? null;
          $range = request()->input('range') ?? 'all';

          // Return summary charts for selected fa representative (individual)
          $types_array = ['first-year', 'renewal', 'trailer', 'advisory-fee', 'general-insurance', 'elite', 'incentives', 'others', 'overridding', 'total'];
          foreach($types_array as $types_index) { $payroll_summary[$types_index] = []; }

          switch ($range) {
            case 'month':
              $earliest = false;
              $earliest_batch = PayrollBatch::orderBy('year')->orderBy('month')->first();
              $latest = false;
              $latest_batch = PayrollBatch::orderByDesc('year')->orderByDesc('month')->first();

              if ($batch = PayrollBatch::where('year', $year)->where('month', $month)->first()) {
                $selected_year = $batch->year;
                $selected_month = $batch->month;
                if ($batch->is($earliest_batch)) $earliest = true;
                if ($batch->is($latest_batch)) $latest = true;
              } else if ($latest_batch) {
                $selected_year = $latest_batch->year;
                $selected_month = $latest_batch->month;
                $latest = true;
              } else {
                // No payroll batch has been released yet in the selected range
                return Common::reject(400, 'invalid_request');
              }

              $db_raw = DB::connection('lfa_payroll')
                                   ->table('computations as payroll_comp')
                                   ->select(
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "first-year" and payroll_cat_slug not in ("elite-scheme", "incentives") then amount end),0) as first_year'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type in ("renewal", "bupa") then amount end),0) as renewal'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "trailer" then amount end),0) as trailer'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "advisory-fee" then amount end),0) as advisory_fee'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "gi" then amount end),0) as general_insurance'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "lfa" and payroll_cat_slug = "elite-scheme" then amount end),0) as elite'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "lfa" and payroll_cat_slug = "incentives" then amount end),0) as incentives'),
                                        DB::raw('coalesce(sum(case when payroll_era = "adjustments" then amount end),0) as others'),
                                        DB::raw('coalesce(sum(case when commission_tier in (2,3) and payroll_era <> "adjustments" then amount end),0) as overridding'),
                                        DB::raw('coalesce(sum(amount),0) as total'))
                                   ->where('payroll_comp.payee_uuid', $selected_associate->uuid)
                                   ->where('payroll_comp.year', $selected_year)
                                   ->where('payroll_comp.month', $selected_month)
                                   // ->where('payroll_comp.payroll_era', '<>', 'aa')
                                   // ->where('payroll_comp.commission_tier', 1)
                                   ->get()->first();
              $computation_single = collect($db_raw)->all();

              $payroll_summary['first-year'] = number_format((float)$computation_single['first_year'] ?? 0, 2, '.', '');
              $payroll_summary['renewal'] = number_format((float)$computation_single['renewal'] ?? 0, 2, '.', '');
              $payroll_summary['trailer'] = number_format((float)$computation_single['trailer'] ?? 0, 2, '.', '');
              $payroll_summary['advisory-fee'] = number_format((float)$computation_single['advisory_fee'] ?? 0, 2, '.', '');
              $payroll_summary['general-insurance'] = number_format((float)$computation_single['general_insurance'] ?? 0, 2, '.', '');
              $payroll_summary['elite'] = number_format((float)$computation_single['elite'] ?? 0, 2, '.', '');
              $payroll_summary['incentives'] = number_format((float)$computation_single['incentives'] ?? 0, 2, '.', '');
              $payroll_summary['others'] = number_format((float)$computation_single['others'] ?? 0, 2, '.', '');
              $payroll_summary['overridding'] = number_format((float)$computation_single['overridding'] ?? 0, 2, '.', '');
              $payroll_summary['total'] = number_format((float)$computation_single['total'] ?? 0, 2, '.', '');

              return response()->json([
                'summary' => $payroll_summary,
                'data' => [
                  'earliest' => $earliest,
                  'latest' => $latest,
                  'range' => $range,
                  'year' => $selected_year,
                  'month' => $selected_month,
                  'month_name' => Carbon::parse($selected_year . '-' . $selected_month)->format('F'),
                  'submissions_count' => $selected_associate->submissions()->count(),
                  'policies_count' => $selected_associate->policies()->count(),
                  'clients_count' => $selected_associate->clients()->where('is_lead', false)->count(),
                ],
                'error' => false,
              ]);

              break;

            case 'year':
              $earliest = false;
              $earliest_batch = PayrollBatch::orderBy('year')->first();
              $latest = false;
              $latest_batch = PayrollBatch::orderByDesc('year')->first();

              if ($batch = PayrollBatch::where('year', $year)->first()) {
                $selected_year = $batch->year;
                if ($batch->year == $earliest_batch->year) $earliest = true;
                if ($batch->year == $latest_batch->year) $latest = true;
              } else if ($latest_batch = PayrollBatch::orderByDesc('year')->first()) {
                $selected_year = $latest_batch->year;
                $latest = true;
              } else {
                // No payroll batch has been released yet in the selected range
                return Common::reject(400, 'invalid_request');
              }

              $months_array = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

              $monthly_raw = DB::connection('lfa_payroll')
                                   ->table('computations as payroll_comp')
                                   ->select('payroll_comp.month',
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "first-year" and payroll_cat_slug not in ("elite-scheme", "incentives") then amount end),0) as first_year'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "renewal" then amount end),0) as renewal'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "trailer" then amount end),0) as trailer'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "advisory-fee" then amount end),0) as advisory_fee'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "gi" then amount end),0) as general_insurance'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "lfa" and payroll_cat_slug = "elite-scheme" then amount end),0) as elite'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "lfa" and payroll_cat_slug = "incentives" then amount end),0) as incentives'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "adjustments" then amount end),0) as others'),
                                        DB::raw('coalesce(sum(case when commission_tier in (2,3) then amount end),0) as overridding'),
                                        DB::raw('coalesce(sum(amount),0) as total'))
                                   ->where('payroll_comp.payee_uuid', $selected_associate->uuid)
                                   // ->where('payroll_comp.payroll_era', '<>', 'aa')
                                   ->where('payroll_comp.year', $selected_year)
                                   // ->where('payroll_comp.commission_tier', 1)
                                   ->groupBy('payroll_comp.month')->orderBy('payroll_comp.month')
                                   ->get();
              $computation_month = collect($monthly_raw)->keyBy('month')->all();

              foreach($months_array as $month) {
                $first_year = $computation_month[$month]->first_year ?? 0; array_push($payroll_summary['first-year'], number_format((float)$first_year, 2, '.', ''));
                $renewal = $computation_month[$month]->renewal ?? 0; array_push($payroll_summary['renewal'], number_format((float)$renewal, 2, '.', ''));
                $trailer = $computation_month[$month]->trailer ?? 0; array_push($payroll_summary['trailer'], number_format((float)$trailer, 2, '.', ''));
                $advisory_fee = $computation_month[$month]->advisory_fee ?? 0; array_push($payroll_summary['advisory-fee'], number_format((float)$advisory_fee, 2, '.', ''));
                $general_insurance = $computation_month[$month]->general_insurance ?? 0; array_push($payroll_summary['general-insurance'], number_format((float)$general_insurance, 2, '.', ''));
                $elite = $computation_month[$month]->elite ?? 0; array_push($payroll_summary['elite'], number_format((float)$elite, 2, '.', ''));
                $incentives = $computation_month[$month]->incentives ?? 0; array_push($payroll_summary['incentives'], number_format((float)$incentives, 2, '.', ''));
                $others = $computation_month[$month]->others ?? 0; array_push($payroll_summary['others'], number_format((float)$others, 2, '.', ''));
                $overridding = $computation_month[$month]->overridding ?? 0; array_push($payroll_summary['overridding'], number_format((float)$overridding, 2, '.', ''));
                $total = $computation_month[$month]->total ?? 0; array_push($payroll_summary['total'], number_format((float)$total, 2, '.', ''));
              }

              return response()->json([
                'summary' => $payroll_summary,
                'data' => [
                  'earliest' => $earliest,
                  'latest' => $latest,
                  'range' => $range,
                  'year' => $selected_year,
                  'month' => null,
                  'submissions_count' => $selected_associate->submissions()->count(),
                  'policies_count' => $selected_associate->policies()->count(),
                  'clients_count' => $selected_associate->clients()->where('is_lead', false)->count(),
                ],
                'error' => false,
              ]);
              break;

            case 'all-time':
              $earliest = true;
              $latest = true;
              $years_array = [];
              for ($i = 2016; $i <= $year; $i++) { array_push($years_array, (string) $i); }

              $yearly_raw = DB::connection('lfa_payroll')
                                   ->table('computations as payroll_comp')
                                   ->select('payroll_comp.year',
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "first-year" and payroll_cat_slug not in ("elite-scheme", "incentives") then amount end),0) as first_year'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "renewal" then amount end),0) as renewal'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "trailer" then amount end),0) as trailer'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era <> "gi" and commission_type = "advisory-fee" then amount end),0) as advisory_fee'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "gi" then amount end),0) as general_insurance'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "lfa" and payroll_cat_slug = "elite-scheme" then amount end),0) as elite'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "lfa" and payroll_cat_slug = "incentives" then amount end),0) as incentives'),
                                        DB::raw('coalesce(sum(case when commission_tier = 1 and payroll_era = "adjustments" then amount end),0) as others'),
                                        DB::raw('coalesce(sum(case when commission_tier in (2,3) then amount end),0) as overridding'),
                                        DB::raw('coalesce(sum(amount),0) as total'))
                                   ->where('payroll_comp.payee_uuid', $selected_associate->uuid)
                                   // ->where('payroll_comp.payroll_era', '<>', 'aa')
                                   ->where('payroll_comp.commission_tier', 1)
                                   ->groupBy('payroll_comp.year')->orderBy('payroll_comp.year')
                                   ->get();
              $computation_all = collect($yearly_raw)->keyBy('year')->all();

              foreach($years_array as $year) {
                $first_year = $computation_all[$year]->first_year ?? 0; array_push($payroll_summary['first-year'], number_format((float)$first_year, 2, '.', ''));
                $renewal = $computation_all[$year]->renewal ?? 0; array_push($payroll_summary['renewal'], number_format((float)$renewal, 2, '.', ''));
                $trailer = $computation_all[$year]->trailer ?? 0; array_push($payroll_summary['trailer'], number_format((float)$trailer, 2, '.', ''));
                $advisory_fee = $computation_all[$year]->advisory_fee ?? 0; array_push($payroll_summary['advisory-fee'], number_format((float)$advisory_fee, 2, '.', ''));
                $general_insurance = $computation_all[$year]->general_insurance ?? 0; array_push($payroll_summary['general-insurance'], number_format((float)$general_insurance, 2, '.', ''));
                $elite = $computation_all[$year]->elite ?? 0; array_push($payroll_summary['elite'], number_format((float)$elite, 2, '.', ''));
                $incentives = $computation_all[$year]->incentives ?? 0; array_push($payroll_summary['incentives'], number_format((float)$incentives, 2, '.', ''));
                $others = $computation_all[$year]->others ?? 0; array_push($payroll_summary['others'], number_format((float)$others, 2, '.', ''));
                $overridding = $computation_all[$year]->overridding ?? 0; array_push($payroll_summary['overridding'], number_format((float)$overridding, 2, '.', ''));
                $total = $computation_all[$year]->total ?? 0; array_push($payroll_summary['total'], number_format((float)$total, 2, '.', ''));
              }

              return response()->json([
                'summary' => $payroll_summary,
                'data' => [
                  'earliest' => $earliest,
                  'latest' => $latest,
                  'range' => $range,
                  'year' => null,
                  'month' => null,
                  'submissions_count' => $selected_associate->submissions()->count(),
                  'policies_count' => $selected_associate->policies()->count(),
                  'clients_count' => $selected_associate->clients()->where('is_lead', false)->count(),
                ],
                'error' => false,
              ]);
              break;

            default:
              // range must be a valid variable
              return Common::reject(400, 'invalid_request');
          }
        } else {
            // Forbidden HTTP Request
          return Common::reject(401, 'unauthorized_user');
        }

    }

























    /** ===================================================================================================
     * Get the providers breakdown for authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardProviders()
    {
        // Declare main variables
        $year = request()->input('year') ?? Carbon::now()->year;
        $provider_data = [];

        // Get the currently authenticated user && associate
        $user = auth()->user();
        $user->last_seen = $user->freshTimestamp();
        $user->save();

        // Get the type of providers breakdown to present
        // There is no default type..
        $summary_type = request()->input('summary_type') ?? null;

        // Get the associate currently retrieved..
        // Lets check if user has permission to view associate's data
        $has_associate_access = false;
        if (($summary_type == 'fa-representative' || $summary_type == 'team') && $associate = Associate::firstUuid(request()->input('associate_uuid'))) {
          $has_associate_access = $user->is_admin || $user->hasAssociate($associate);
        }

        if ($summary_type == 'fa-representative' && $has_associate_access) {
          // Return summary charts for selected fa representative (individual)
          $monthly_raw = DB::connection('lfa_payroll')
                               ->table('records')
                               ->join('lfa_selections._lfa_providers', 'provider_slug', '=', '_lfa_providers.slug')
                               ->select(
                                  '_lfa_providers.full_name',
                                  '_lfa_providers.alias',
                                  '_lfa_providers.color',
                                  DB::raw('count(DISTINCT(policy_uuid)) as count'))
                               ->whereRaw('YEAR(date_inception) = ' . $year . ' AND associate_uuid = "' . $associate->uuid . '"')
                               ->groupBy('provider_slug')
                               ->get();
          $computation = collect($monthly_raw)->all();
        } else if ($summary_type == 'team' && $has_associate_access && $associate->is_manager) {
          // Return summary charts for selected fa representative (team)
          // Ensure user has access to retrieve associate records
          // Ensure associate has access to retrieve overridding commissions records
          $all_agents_array = $associate->active_sales_agents()->pluck('uuid');
          $monthly_raw = DB::connection('lfa_payroll')
                               ->table('records')
                               ->join('lfa_selections._lfa_providers', 'provider_slug', '=', '_lfa_providers.slug')
                               ->select(
                                  '_lfa_providers.full_name',
                                  '_lfa_providers.alias',
                                  '_lfa_providers.color',
                                  DB::raw('count(DISTINCT(policy_uuid)) as count'))
                               ->whereIn('associate_uuid', $all_agents_array)
                               ->whereRaw('YEAR(date_inception) = ' . $year)
                               ->groupBy('provider_slug')
                               ->get();
          $computation = collect($monthly_raw)->all();
        } else {
          // Return summary charts for organisation (admin-only)
          // Ensure user is admin
          //  } else if ($summary_type == 'organisation' && $user->is_admin) {
          // Return basic summary charts for firm-wide
          $monthly_raw = DB::connection('lfa_payroll')
                               ->table('records')
                               ->join('lfa_selections._lfa_providers', 'provider_slug', '=', '_lfa_providers.slug')
                               ->select(
                                  '_lfa_providers.full_name',
                                  '_lfa_providers.alias',
                                  '_lfa_providers.color',
                                  DB::raw('count(DISTINCT(policy_uuid)) as count'))
                               ->whereRaw('YEAR(date_inception) = ' . $year)
                               ->groupBy('provider_slug')
                               ->get();
          $computation = collect($monthly_raw)->all();
        }

        return response()->json([
          'provider_data' => $computation ?? null,
          'error' => false
        ]);
    }
}
