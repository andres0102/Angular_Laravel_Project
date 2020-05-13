<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\LegacyFA\Submissions\SubmissionCaseInfo;
use App\Transformers\Data_SubmissionCaseTransformer;

class SubmissionCaseHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($submission = null)
    {
        // Media :: Client Identity
        // $media_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
        //                     'id', id,
        //                     'file_name', file_name,
        //                     'collection_name', collection_name,
        //                     'mime_type', mime_type,
        //                     'size', size
        //                 )) FROM lfa__default.media WHERE model_type = 'submissions_cases' AND model_id = case.id) as media";
        $cases_riders_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'uuid', rider_uuid,
                            'name', rider_name,
                            'gst_rate', gst_rate,
                            'sum_assured', sum_assured,
                            'policy_term', policy_term,
                            'payment_term', payment_term,
                            'gross_payment_before_gst', gross_payment_before_gst,
                            'gross_payment_gst', gross_payment_gst,
                            'gross_payment_after_gst', gross_payment_after_gst,
                            'payment_discount', payment_discount,
                            'nett_payment_before_gst', nett_payment_before_gst,
                            'nett_payment_gst', nett_payment_gst,
                            'nett_payment_after_gst', nett_payment_after_gst
                        )) FROM lfa_submissions.case_riders WHERE case_uuid = case.uuid) as riders";
        $cases_documents_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'title', title,
                            'value', value
                        )) FROM lfa_submissions.case_info WHERE case_uuid = case.uuid) as documents";
        $query = DB::connection('lfa_submissions')
                    ->table('cases as case')
                    // ->leftJoin('lfa_clients.life_assured', 'case.life_assured_uuid', '=', 'life_assured.uuid')
                    ->leftJoin('lfa_selections._lfa_providers as provider', 'case.provider_slug', '=', 'provider.slug')
                    ->select(
                        'case.*',
                        'provider.full_name as provider_name',
                        'provider.alias as provider_alias',
                        'provider.color as provider_color',
                        'provider.background as provider_background',
                        // 'life_assured.is_client as life_assured_is_client',
                        DB::raw('(SELECT date_submission from lfa_submissions.submissions where submissions.uuid = case.submission_uuid) as date_submission'),
                        // DB::raw('(SELECT title from lfa_selections.relationship_types where relationship_types.slug = life_assured.relationship_type_slug) as life_assured_relationship'),
                        // Individual Data
                        // DB::raw($individual_query . ' WHERE individuals.uuid = life_assured.individual_uuid) as life_assured'),
                        DB::raw($cases_riders_query),
                        DB::raw($cases_documents_query)
                        // DB::raw($media_query)
                    );

        if ($submission) {
            $query = $query->where('case.submission_uuid', $submission->uuid);
        }

        return fractal($query->get()->toArray(), new Data_SubmissionCaseTransformer())->toArray()['data'];
    }


    public static function downloadFromOldPortal($case_uuid, $file_type) {
        $case_info = SubmissionCaseInfo::where('case_uuid', $case_uuid)->get();
        if ($case_info && in_array($file_type, ['doc_nric', 'doc_app', 'doc_bi', 'doc_supporting', 'doc_pfr']) && $case_info->where('title', $file_type)->isNotEmpty()) {
            $original = $case_info->where('title', $file_type . '_original')->first()->value;
            $file = $case_info->where('title', $file_type)->first()->value;
            $tempImage = tempnam(sys_get_temp_dir(), $file);
            copy('http://192.168.7.247/uploads/submissions/'.$file, $tempImage);
            return response()->download($tempImage, $original)->deleteFileAfterSend(true);
        } else {
            return Common::reject(404, 'file_not_found');
        }
    }
}