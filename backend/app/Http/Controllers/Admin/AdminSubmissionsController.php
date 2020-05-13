<?php

namespace App\Http\Controllers\Admin;

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

class AdminSubmissionsController extends Controller
{
    /**
     * Create a new AdminSubmissionsController instance.
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
        if ($user->can('submissions_mgmt_view')) {
            return response()->json([
                'error' => false,
                'data' => SubmissionHelper::index(null, null, null, true)
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
        if ($user->can('submissions_mgmt_view')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => SubmissionHelper::index($submission)
                ]);
            } else {
                return Common::reject(404, 'submission_not_found');
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
    public function getCases($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_view')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => SubmissionCaseHelper::index($submission)
                ]);
            } else {
              return Common::reject(404, 'submission_not_found');
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
        if ($user->can('submissions_mgmt_view')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => ActivityLogHelper::index('submissions', $submission)
                ]);
            } else {
              return Common::reject(404, 'submission_not_found');
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
    public function getMedia($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_view')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
                return response()->json([
                    'error' => false,
                    'data' => MediaHelper::index('submissions', $submission)
                ]);
            } else {
              return Common::reject(404, 'submission_not_found');
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
    public function getSubmissionMedia($uuid, $media_id)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_view')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
              $media = $submission->documents->where('id', $media_id)->first();
              // return response()->download($media->id .'/'.$media->file_name);
              // return response()->download('app/submissions/'.$media->id .'/'.$media->file_name);
              return Storage::disk('submissions')->download($media->id .'/'.$media->file_name);
            } else {
              return Common::reject(404, 'submission_not_found');
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
    public function getCaseMedia($uuid, $case_uuid, $media_id)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_view')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
              if ($case = $submission->cases()->where('uuid', $case_uuid)->first()) {
                $media = $case->documents->where('id', $media_id)->first();
                // return response()->download($media->id .'/'.$media->file_name);
                // return response()->download('app/submissions/'.$media->id .'/'.$media->file_name);
                return Storage::disk('submissions')->download($media->id .'/'.$media->file_name);
              } else {
                return Common::reject(404, 'case_not_found');
              }
            } else {
              return Common::reject(404, 'submission_not_found');
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
    public function downloadFromOldPortal($case_uuid, $file_type)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_view')) {
            if ($submission = SubmissionCase::firstUuid($case_uuid)) {
                return SubmissionCaseHelper::downloadFromOldPortal($case_uuid, $file_type);
            } else {
              return Common::reject(404, 'case_not_found');
            }
        } else {
          return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_delete')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
              $submission->log($user, 'submission_deleted', 'Submission record deleted.', $submission);
              $submission->delete();
              return response()->json(['error' => false]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function restore($uuid)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_update')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
              $submission->restore();
              $submission->log($user, 'submission_restored', 'Submission record restored.', null, $submission);
              return response()->json([
                  'error' => false,
                  'data' => SubmissionHelper::index($submission)
              ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function updateStatus($uuid, $status_slug)
    {
        // Check if authenticated user has permission to execute this action
        $user = auth()->user();
        if ($user->can('submissions_mgmt_update')) {
            if ($submission = Submission::withTrashed()->firstUuid($uuid)) {
              switch($status_slug) {
                case 'pending-verification':
                  $submission->log($user, 'submission_approved_screening', 'Submission status update :: Compliance Screening Approved.', $submission);
                  $submission->update(['status_slug' => $status_slug]);
                  break;
                case 'pending-submission':
                  $submission->log($user, 'submission_approved_verification', 'Submission status update :: Verification Approved.', $submission);
                  $submission->update(['status_slug' => $status_slug]);
                  break;
                case 'submitted':
                  $submission->log($user, 'submission_submitted', 'Submission status update :: Submitted to Provider.', $submission);
                  $submission->update(['status_slug' => $status_slug]);

                  // Check Submission for Introducer Cases
                  $introducer_case = false;
                  $client = $submission->policy_holder;
                  if ($client->introducers()->where('year', ($submission->date_submission->year ?? Carbon::now()->year))->exists()) {
                    // Client is registered as an introducer
                    $introducer_case = 'introducer';
                    $introducer = $client->introducers()->orderByDesc('year')->first();
                  } else if ($client->who_is_nominee()->exists()) {
                    $introducer_case = 'nominee';
                    // Client is registered as a nominee
                    $client->update(['is_lead' => true, 'lead_stage_slug' => 'converted-to-client']);
                    $nominee = $client->who_is_nominee;
                    $nominee->update(['converted' => true]);
                    $introducer = $nominee->introducer;
                  }

                  if ($introducer_case) {
                    foreach ($submission->cases as $case) {
                      $introducer_case_primary = [
                        'submission_uuid' => $submission->uuid,
                        'associate_uuid' => $submission->associate_uuid,
                        'client_uuid' => $submission->client_uuid,
                        'case_uuid' => $case->uuid,
                      ];

                      $introducer_case_snapshot = [
                        'associate_name' => $submission->associate_name,
                        'introducer_uuid' => $introducer->uuid,
                        'introducer_name' => $introducer->name,
                        'nominee_uuid' => ($nominee) ? $nominee->uuid : null,
                        'nominee_name' => ($nominee) ? $client->name : null,
                        'client_name' => $submission->client_name,
                        'client_nric_no' => $client->individual->nric_no,
                        'life_assured_uuid' => $case->life_assured_uuid,
                        'life_assured_name' => $case->life_assured_name,
                        'life_assured_is_client' => $case->life_assured_is_client,
                        'provider_name' => $case->provider_name,
                        'submission_category' => $case->submission_category,
                        'product_category' => $case->product_category,
                        'product_name' => $case->product_name,
                        'option_name' => $case->option_name,
                        'payment_term' => $case->payment_term,
                        'payment_frequency' => $case->payment_frequency,
                        'payment_type' => $case->payment_type,
                        'gst_rate' => $case->gst_rate,
                        'gross_payment_before_gst' => $case->gross_payment_before_gst,
                        'gross_payment_gst' => $case->gross_payment_gst,
                        'gross_payment_after_gst' => $case->gross_payment_after_gst,
                        'payment_discount' => $case->payment_discount,
                        'nett_payment_before_gst' => $case->nett_payment_before_gst,
                        'nett_payment_gst' => $case->nett_payment_gst,
                        'nett_payment_after_gst' => $case->nett_payment_after_gst,
                        'payment_mode' => $case->payment_mode,
                        'currency' => $case->currency,
                        'ape' => $case->ape
                      ];

                      if ($introducer_case == 'introducer') {
                        $bank_info = $client->individual->banks->first();
                        $introducer_additional_array = [
                          'introducer_scheme_type' => 'introducer',
                          'scheme_receiver_type' => 'introducer',
                          'scheme_receiver_name' => $submission->client_name,
                          'scheme_bank_name' => ($bank_info) ? $bank_info->bank->full_name : null,
                          'scheme_bank_account_no' => ($bank_info) ? $bank_info->account_no : null,
                        ];
                      } else if ($introducer_case == 'nominee') {
                        switch ($nominee->nominee_benefit_slug) {
                          case 'receive':
                            $receiver_type = 'introducer';
                            $receiver_name = $introducer->name;
                            break;
                          case 'donate':
                            $receiver_type = 'charity';
                            $receiver_name = null;
                            break;
                          default:
                            $receiver_type = 'nominee';
                            $receiver_name = $submission->client_name;
                        }

                        $bank_info = $nominee->individual->banks->first();
                        $introducer_additional_array = [
                          'introducer_scheme_type' => 'nominee',
                          'scheme_receiver_type' => $receiver_type,
                          'scheme_receiver_name' => $receiver_name,
                          'scheme_bank_name' => ($bank_info) ? $bank_info->bank->full_name : null,
                          'scheme_bank_account_no' => ($bank_info) ? $bank_info->account_no : null,
                        ];
                      }

                      $introducer_case = IntroducerCase::updateOrCreate($introducer_case_primary, array_merge($introducer_case_snapshot, $introducer_additional_array));

                    } // end foreach
                  }

                  break;
                case 'rejected':
                  $submission->log($user, 'submission_rejected', 'Submission Rejected: ' . request()->input('message'), $submission);
                  $submission->update(['status_slug' => $status_slug, 'remarks' => request()->input('message')]);
                  break;
              }
              return response()->json([
                  'error' => false,
                  'data' => SubmissionHelper::index($submission)
              ]);
            } else {
              return Common::reject(404, 'submission_not_found');
            }
        } else {
            return Common::reject(401, 'unauthorized_user');
        }
    }









}
