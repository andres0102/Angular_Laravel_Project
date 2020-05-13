<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Transformers\Data_SubmissionTransformer;

class SubmissionHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($submission = null, $associate = null, $client = null, $show_all = false, $associate_uuid_array = null)
    {
        // Media :: Client Identity
        // $media_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
        //                     'id', id,
        //                     'file_name', file_name,
        //                     'collection_name', collection_name,
        //                     'mime_type', mime_type,
        //                     'size', size
        //                 )) FROM lfa__default.media WHERE model_type = 'submissions' AND model_id = submission.id) as media";

        $query = DB::connection('lfa_submissions')
                    ->table('submissions as submission')
                    ->select(
                        'submission.*',
                        DB::raw("(SELECT title FROM lfa_selections._lfa_submission_status WHERE slug = submission.status_slug) as status"),
                        DB::raw("(SELECT description FROM lfa_selections._lfa_submission_status WHERE slug = submission.status_slug) as status_desc"),
                        DB::raw("(SELECT count(*) FROM lfa_submissions.cases WHERE submission_uuid = submission.uuid) as case_count"),
                        DB::raw("(SELECT count(*) FROM lfa__default.media WHERE model_type = 'submissions' AND model_id = submission.id) as media_count"),
                        DB::raw("(SELECT GROUP_CONCAT(DISTINCT _lfa_providers.alias SEPARATOR ', ') FROM lfa_submissions.cases LEFT JOIN lfa_selections._lfa_providers ON provider_slug = _lfa_providers.slug WHERE submission_uuid = submission.uuid ORDER BY _lfa_providers.alias) as provider_names"),
                        DB::raw("(SELECT SUM(nett_payment_after_gst) FROM lfa_submissions.cases WHERE submission_uuid = submission.uuid) as premiums"),
                        DB::raw("(SELECT SUM(ape) FROM lfa_submissions.cases WHERE submission_uuid = submission.uuid) as ape")
                    )->orderByDesc('submission.date_submission');

        if ($submission) {
            $results = $query->where('submission.uuid', $submission->uuid);
            return fractal($results->first(), new Data_SubmissionTransformer())->toArray()['data'];
        } else {
            if (!$show_all) $query = $query->where('submission.deleted_at', null);

            if ($associate_uuid_array) $query = $query->whereIn('associate_uuid', $associate_uuid_array);

            if ($associate) {
                $results_assoc = $query->where('submission.associate_uuid', $associate->uuid);
            } else {
                $results_assoc = $query;
            }

            if ($client) {
                $results = $results_assoc->where('submission.client_uuid', $client->uuid);
            } else {
                $results = $results_assoc;
            }

            return fractal($results->get()->toArray(), new Data_SubmissionTransformer())->toArray()['data'];
        }
    }


    /** ===================================================================================================
     * Function to return validations for table fields
     *
     */
    public static function validations($type = null, $required = false)
    {
      $required_str = ($required) ? 'required|' : '';

      switch ($type) {
        default:
            return [
                'client_type_slug' => 'nullable|string|exists:lfa_selections._lfa_client_types,slug',
                'associate_uuid' => 'uuid|exists:lfa_associates.associates,uuid',
                'individual_uuid' => 'uuid|exists:lfa_individuals.individuals,uuid',
                'is_lead' => 'boolean',
                'lead_stage_slug' => 'nullable|string|exists:lfa_selections._lfa_lead_stage,slug',
                'source_slug' => 'nullable|string|exists:lfa_selections._lfa_client_sources,slug',
                'display_name' => 'required|string',
                'business_name' => 'nullable|string',
                'business_uen' => 'nullable|string',
            ];
      }
    }


    /** ===================================================================================================
     * Function to return valid table fields
     *
     */
    public static function fields($type = null)
    {
      return collect(self::validations($type))->keys()->all();
    }


    /** ===================================================================================================
    * Create Associate record
    * via App\Http\Controllers\Admin\AdminAssociatesClientsController
    **/
    public static function create($data, $model_uuid = null, $type = null)
    {
        switch ($type) {
            case '':
                break;
        } // end switch
    }


    /** ===================================================================================================
    * Map data record
    **/
    public static function map($data, $type = null)
    {
        switch ($type) {
            case 'info':
                return $data->map(function ($item) {
                    return [
                        $item->title => $item->value
                    ];
                });
                break;
            case 'riders':
                return $data->map(function ($item) {
                    return [
                        'rider_name' => $item->rider_name
                    ];
                });
                break;
            case 'cases':
                return $data->map(function ($item) {
                    $case_uuid = $item->uuid;
                    $info = collect(DB::connection('lfa_submissions')
                                ->table('case_info as info')
                                ->where('case_uuid', '=', $case_uuid)
                                ->get()->toArray());
                    $info_array = ($info->count()) ? self::map($info, 'info')->collapse()->toArray() : [];
                    $riders = collect(DB::connection('lfa_submissions')
                                ->table('case_riders as rider')
                                ->where('case_uuid', '=', $case_uuid)
                                // ->leftJoin('lfa_products.riders', 'riders.uuid', '=', 'rider.rider_uuid')
                                ->get()->toArray());

                    return array_merge([
                        'uuid' => $item->uuid,
                        'provider' => $item->provider_name,
                        'provider_alias' => $item->provider_alias,
                        'submission_category' => $item->submission_category,
                        'submission_mode' => $item->submission_mode,
                        'product_category' => $item->product_category,
                        'product_name' => $item->product_name,
                        'option_name' => $item->option_name,
                        'currency' => $item->currency,
                        'ape' => $item->ape,
                        'payment_term' => $item->payment_term,
                        'payment_frequency' => $item->payment_frequency,
                        'payment_type' => $item->payment_type,
                        'payment' => $item->payment,
                        'payment_gst' => $item->payment_gst,
                        'payment_mode' => $item->payment_mode,
                        'reference_no' => $item->reference_no,
                        'riders' => self::map($riders, 'riders')->pluck('rider_name')
                    ], $info_array);
                });
                break;
        } // end switch
    }
}