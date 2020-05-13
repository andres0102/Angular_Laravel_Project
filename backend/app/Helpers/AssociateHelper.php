<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Helpers\UserHelper;
use App\Models\Users\User;
use App\Models\Individuals\Individual;
use App\Models\LegacyFA\Associates\Associate;
use App\Transformers\Data_AssociateTransformer;

class AssociateHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($associate = null)
    {
        $co_last_day = Carbon::parse(env('CO_LAST_DAY'))->format('Y-m-d');

        // Get associates earliest movements
        $earliest_movement = DB::connection('lfa_associates')
                ->table('lfa_associates.movements')
                ->select('associate_uuid', DB::raw('min(date_start) as min_date'))->groupBy('associate_uuid');

        // Get associates latest movements
        $latest_movement = DB::connection('lfa_associates')
                ->table('lfa_associates.movements')
                ->select('associate_uuid', DB::raw('max(date_end) as max_date'))->groupBy('associate_uuid');

        // Individual Data
        $individual_contact_query = "(SELECT JSON_OBJECT(
                            'home_no', home_no,
                            'mobile_no', mobile_no,
                            'fax_no', fax_no,
                            'email', email
                        ) FROM lfa_individuals.contacts WHERE contacts.individual_uuid = uuid AND contact_type_slug = 'default')";
        $individual_address_query = "(SELECT JSON_OBJECT(
                            'block', block,
                            'street', street,
                            'unit', unit,
                            'building', building,
                            'city', city,
                            'postal', postal,
                            'country_slug', country_slug
                        ) FROM lfa_individuals.addresses WHERE addresses.individual_uuid = uuid AND address_type_slug = 'residential')";
        $individual_query = "(SELECT JSON_OBJECT(
                            'individual_uuid', uuid,
                            'salutation_slug', salutation_slug,
                            'full_name', full_name,
                            'alias', alias,
                            'chinese_name', chinese_name,
                            'nric_no', nric_no,
                            'fin_no', fin_no,
                            'passport_no', passport_no,
                            'gender_slug', gender_slug,
                            'date_birth', date_birth,
                            'race_slug', race_slug,
                            'country_birth_slug', country_birth_slug,
                            'nationality_slug', nationality_slug,
                            'residency_status_slug', residency_status_slug,
                            'marital_status_slug', marital_status_slug,
                            'employment_status_slug', employment_status_slug,
                            'income_range', income_range,
                            'job_title', job_title,
                            'company_name', company_name,
                            'business_nature', business_nature,
                            'education_level_slug', education_level_slug,
                            'education_institution', education_institution,
                            'field_of_study', field_of_study,
                            'smoker', smoker,
                            'selected', selected,
                            'pdpa', pdpa,
                            'contact_information', " . $individual_contact_query . ",
                            'address_information', " . $individual_address_query . "
                        ) FROM lfa_individuals.individuals WHERE uuid = user.individual_uuid) as individual";

        // Provider Codes
        $providers_codes_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'uuid', uuid,
                            'provider_slug', provider_slug,
                            'code', code
                        )) FROM lfa_associates.providers_codes WHERE associate_uuid = associate.uuid) as providers_codes";

        // Movements
        $movements_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'uuid', uuid,
                            'designation_slug', designation_slug,
                            'reporting_uuid', reporting_uuid,
                            'aa_code', aa_code,
                            'lfa_code', lfa_code,
                            'date_start', date_start,
                            'date_end', date_end
                        )) FROM lfa_associates.movements WHERE associate_uuid = associate.uuid ORDER BY date_end) as movements";

        // Bandings LFA
        $bandings_lfa_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'uuid', uuid,
                            'banding_type', banding_type,
                            'rank', rank,
                            'rate', rate,
                            'date_start', date_start,
                            'date_end', date_end
                        )) FROM lfa_associates.bandings_lfa WHERE associate_uuid = associate.uuid ORDER BY date_end) as bandings_lfa";

        // Bandings GI
        $bandings_gi_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'uuid', uuid,
                            'rank', rank,
                            'date_start', date_start,
                            'date_end', date_end
                        )) FROM lfa_associates.bandings_gi WHERE associate_uuid = associate.uuid ORDER BY date_end) as bandings_gi";

        // Begin to merge DB tables
        $query = DB::connection('lfa_associates')
                    ->table('associates as associate')
                    ->where('associate.deleted_at', null)
                    ->leftJoin('lfa_users.users as user', 'associate.uuid', '=', 'user.associate_uuid')
                    ->leftJoin('lfa_individuals.individuals as spouse', 'user.spouse_uuid', '=', 'spouse.uuid')
                    ->leftJoin('lfa_users.users as spouse_user', 'spouse.uuid', '=', 'spouse_user.individual_uuid')
                    ->leftJoin('lfa_associates.associates as spouse_assoc', 'spouse_user.associate_uuid', '=', 'spouse_assoc.uuid')
                    ->leftJoin('lfa__default.media', function($join)
                        {
                            $join->on('user.id', '=', 'media.model_id');
                            $join->on('model_type','=',DB::raw("'user'"));
                            $join->on('collection_name','=',DB::raw("'display_photo'"));
                        })
                    ->leftJoinSub($earliest_movement, 'earliest_movement', function ($join)
                        {
                            $join->on('associate.uuid', '=', 'earliest_movement.associate_uuid');
                        })
                    ->leftJoin('movements as first_movement', function($join)
                        {
                            $join->on('associate.uuid', '=', 'first_movement.associate_uuid');
                            $join->on('first_movement.date_start', '=', 'earliest_movement.min_date');
                        })
                    ->leftJoinSub($latest_movement, 'latest_movement', function ($join)
                        {
                            $join->on('associate.uuid', '=', 'latest_movement.associate_uuid');
                        })
                    ->leftJoin('movements as last_movement', function($join)
                        {
                            $join->on('associate.uuid', '=', 'last_movement.associate_uuid');
                            $join->on('last_movement.date_end', '=', 'latest_movement.max_date');
                        })
                    ->leftJoin('lfa_selections._lfa_designations as sales_designation', 'last_movement.designation_slug', '=', 'sales_designation.slug')
                    ->select(
                        'user.*',
                        'associate.uuid as uuid',
                        'user.uuid as user_uuid',
                        'user.email as lfa_email',
                        'user.printer_id as lfa_printer_id',
                        'user.did_no as lfa_did_no',
                        DB::raw('ifnull(media.id, null) as profile'),
                        // Individual Data
                        DB::raw($individual_query),
                        DB::raw($providers_codes_query),
                        DB::raw($movements_query),
                        DB::raw($bandings_lfa_query),
                        DB::raw($bandings_gi_query),
                        // Spouse Data
                        'spouse_assoc.uuid as spouse_associate_uuid',
                        'spouse.full_name as spouse_name',
                        'spouse.job_title as spouse_job_title',
                        'spouse.company_name as spouse_company_name',
                        // LFA Data
                        DB::raw('(SELECT title FROM lfa_selections._lfa_onboarding_status WHERE slug = user.onboarding_status_slug) as onboarding_status'),
                        DB::raw('(SELECT title FROM lfa_selections._lfa_designations WHERE slug = user.designation_slug) as designation'),
                        DB::raw('if(last_movement.date_end = "' . $co_last_day . '", true, false) as active'),
                        'sales_designation.title as sales_designation',
                        'first_movement.date_start as first_day',
                        DB::raw('if(last_movement.date_end = "' . $co_last_day . '", null, last_movement.date_end) as last_day'),
                        // Salesforce Data
                        DB::raw('(SELECT title FROM lfa_selections._lfa_associates_rnf_status WHERE slug = associate.rnf_status_slug) as rnf_status'),
                        'associate.rnf_status_slug',
                        'associate.rnf_no',
                        'associate.lfa_sl_no',
                        'associate.applicant_code',
                        'last_movement.lfa_code',
                        'sales_designation.salesforce_tier as tier',
                        DB::raw('if(sales_designation.salesforce = 1, true, false) as is_salesforce'),
                        DB::raw('if(sales_designation.override = 1, true, false) as is_overriding'),
                        DB::raw('if(sales_designation.salesforce_tier = 1, true, false) as is_t1'),
                        DB::raw('if(sales_designation.salesforce_tier = 2, true, false) as is_t2'),
                        DB::raw('if(sales_designation.salesforce_tier = 3, true, false) as is_t3'),
                        DB::raw('(SELECT COUNT(*) from lfa_clients.clients WHERE associate_uuid = associate.uuid) as clients_count'),
                        DB::raw('(SELECT COUNT(*) from lfa_policies.policies WHERE associate_uuid = associate.uuid) as policies_count'),
                        DB::raw('(SELECT COUNT(*) from lfa_submissions.submissions WHERE associate_uuid = associate.uuid) as submissions_count'),
                        // Submissions Data
                        'associate.eligible_life as lfa_eligible_life',
                        'associate.eligible_health as lfa_eligible_health',
                        'associate.eligible_ilp as lfa_eligible_ilp',
                        'associate.eligible_cis as lfa_eligible_cis',
                        'associate.eligible_gi as lfa_eligible_gi',
                        'associate.eligible_medishield as lfa_eligible_medishield',
                        // Dates
                        'associate.date_rnf_submission',
                        'associate.date_rnf_approval',
                        'associate.date_rnf_withdrawal',
                        'associate.date_rnf_cessation',
                        'associate.date_m9',
                        'associate.date_m9a',
                        'associate.date_m5',
                        'associate.date_hi',
                        'associate.date_m8',
                        'associate.date_m8a',
                        'associate.date_cert_ilp',
                        'associate.date_cert_li',
                        'associate.date_cert_fna',
                        'associate.date_cert_bcp',
                        'associate.date_cert_pgi',
                        'associate.date_cert_comgi',
                        'associate.date_cert_cgi',
                        'associate.cert_pro'
                    )->orderBy('name');

        if ($associate) {
            $results = $query->where('associate.uuid', $associate->uuid)->first();
        } else {
            $results = $query->get()->toArray();
        }

        return fractal($results, new Data_AssociateTransformer())->toArray()['data'];
    }


    /** ===================================================================================================
     * Function to return validations for table fields
     *
     */
    public static function validations($type = null, $required = false)
    {
      $required_str = ($required) ? 'required|' : '';

      switch ($type) {
        case 'movements':
            return [
                'lfa_code' => $required_str . 'string|size:9',
                'designation_slug' => $required_str . 'string|exists:lfa_selections._lfa_designations,slug',
                'reporting_uuid' => 'nullable|uuid|exists:lfa_associates.associates,uuid',
                'date_start' => $required_str . 'date',
                'date_end' => 'nullable|date',
            ];
            break;
        case 'bandings_lfa':
            return [
                'banding_type' => $required_str . 'digits:4',
                'rank' => $required_str . 'integer',
                'rate' => $required_str . 'numeric|max:0.8',
                'date_start' => $required_str . 'date',
                'date_end' => $required_str . 'date',
            ];
            break;
        case 'bandings_gi':
            return [
                'rank' => $required_str . 'integer',
                'date_start' => $required_str . 'date',
                'date_end' => $required_str . 'date',
            ];
            break;
        case 'providers_codes':
            return [
                'provider_slug' => $required_str . 'exists:lfa_selections._lfa_providers,slug',
                'code' => $required_str . 'string',
            ];
            break;
        default:
            return [
                'rnf_status_slug' => 'nullable|exists:lfa_selections._lfa_associates_rnf_status,slug',
                'aa_code' => 'nullable',
                'lfa_sl_no' => $required_str . 'digits:4',
                'applicant_code' => 'nullable|string',
                'rnf_no' => 'nullable|string',
                'eligible_life' => 'boolean',
                'eligible_health' => 'boolean',
                'eligible_ilp' => 'boolean',
                'eligible_cis' => 'boolean',
                'eligible_gi' => 'boolean',
                'eligible_medishield' => 'boolean',
                'date_rnf_submission' => 'nullable|date',
                'date_rnf_approval' => 'nullable|date',
                'date_rnf_withdrawal' => 'nullable|date',
                'date_rnf_cessation' => 'nullable|date',
                'date_m9' => 'nullable|date',
                'date_m9a' => 'nullable|date',
                'date_m5' => 'nullable|date',
                'date_hi' => 'nullable|date',
                'date_m8' => 'nullable|date',
                'date_m8a' => 'nullable|date',
                'date_cert_ilp' => 'nullable|date',
                'date_cert_li' => 'nullable|date',
                'date_cert_fna' => 'nullable|date',
                'date_cert_bcp' => 'nullable|date',
                'date_cert_pgi' => 'nullable|date',
                'date_cert_comgi' => 'nullable|date',
                'date_cert_cgi' => 'nullable|date',
                'cert_pro' => 'nullable|string',
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
    * via App\Http\Controllers\Admin\AdminAssociatesController
    **/
    public static function create($data, $model_uuid = null, $type = null)
    {
        switch ($type) {
            case 'movements':
                // Retrieve associate
                $associate = Associate::firstUuid($model_uuid);

                // Date end is empty === latest movement record of associate
                $date_start = Carbon::parse($data['date_start']);
                $date_end = Carbon::parse((Common::validData($data, 'date_end')) ? $data['date_end'] : env('CO_LAST_DAY'));

                // Check if Movement records exists
                if ($associate->movements()->exists()) {
                    // Get all movement records (ordered by date_end)
                    $movements = $associate->movements->sortBy('date_end');
                    $earliest_movement = $movements->first();
                    $latest_movement = $movements->last();

                    // Compare if new record is at the start of end of the spectrum
                    if ($earliest_movement->date_start->lte($date_end) && $earliest_movement->date_end->gt($date_end)) {
                        // New record will replace the earliest record
                        // Amend the current earliest record
                        $earliest_movement->date_start = $date_end->copy()->addDays(1);
                        $earliest_movement->save();
                    } else if ($latest_movement->date_start->lt($date_start) && $latest_movement->date_end->gte($date_start)) {
                        // New record will replace the latest record
                        // Amend the current last record
                        $latest_movement->date_end = $date_start->copy()->subDays(1);
                        $latest_movement->save();
                    } else if (
                        ($earliest_movement->date_start->gte($date_start) && $earliest_movement->date_end->lte($date_end)) ||
                        ($latest_movement->date_start->gte($date_start) && $latest_movement->date_end->lte($date_end))) {
                        return Common::reject(403, 'movements_overlap');
                    }
                }

                // Create new movement record
                $movement = $associate->movements()->create([
                    'lfa_code' => $data['lfa_code'],
                    'designation_slug' => $data['designation_slug'],
                    'reporting_uuid' => $data['reporting_uuid'] ?? null,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                ]);
                // $associate->log(auth()->user(), 'movement_created', 'Created new movement record.', null, $movement->fresh());

                return response()->json([
                    'message' => 'movement_created',
                    'error' => false,
                    'data' => self::index($associate->fresh())
                ]);

                break;

            case 'bandings_lfa':
                // Retrieve associate
                $associate = Associate::firstUuid($model_uuid);
                $date_start = Carbon::parse($data['date_start'])->startOfDay();

                // Calculate next bandng date
                $year_start = $date_start->copy()->startOfYear();
                $year_middle_first = $year_start->copy()->addMonths(5)->endOfMonth()->startOfDay();
                $year_middle_last = $year_middle_first->copy()->addDays(1);
                $year_end = $date_start->copy()->endOfYear()->startOfDay();
                if ($date_start->between($year_start, $year_middle_first)) {
                    $date_end = $year_middle_first;
                } else if ($date_start->between($year_middle_last, $year_end)) {
                    $date_end = $year_middle_last->addYears(1)->subDay();
                }
                $data['date_end'] = $date_end;

                // Check if Banding records exists
                if ($associate->bandings_lfa()->exists()) {
                    // Get all banding records (ordered by date_end)
                    $bandings = $associate->bandings_lfa->sortBy('date_end');
                    $earliest_banding = $bandings->first();
                    $latest_banding = $bandings->last();

                    // Compare if new record is at the start of end of the spectrum
                    if ($earliest_banding->date_start->lte($date_end) && $earliest_banding->date_end->gt($date_end)) {
                        // New record will replace the earliest record
                        // Amend the current earliest record
                        $earliest_banding->date_start = $date_end->copy()->addDays(1);
                        $earliest_banding->save();
                    } else if ($latest_banding->date_start->lt($date_start) && $latest_banding->date_end->gte($date_start)) {
                        // New record will replace the latest record
                        // Amend the current last record
                        $latest_banding->date_end = $date_start->copy()->subDays(1);
                        $latest_banding->save();
                    } else if (
                        ($earliest_banding->date_start->gte($date_start) && $earliest_banding->date_end->lte($date_end)) ||
                        ($latest_banding->date_start->gte($date_start) && $latest_banding->date_end->lte($date_end))) {
                        return Common::reject(403, 'bandings_overlap');
                    }
                }

                // Create new banding record
                $banding = $associate->bandings_lfa()->create($data);
                // $associate->log(auth()->user(), 'banding_lfa_created', 'Created new LFA banding record.', null, $banding->fresh());

                return response()->json([
                    'message' => 'banding_created',
                    'error' => false,
                    'data' => self::index($associate->fresh())
                ]);

                break;

            case 'bandings_gi':
                // Retrieve associate
                $associate = Associate::firstUuid($model_uuid);
                $date_start = Carbon::parse($data['date_start']);

                // Calculate next bandng date
                $year_start = $date_start->copy()->startOfYear();
                $year_middle_first = $year_start->copy()->addMonths(5)->endOfMonth()->startOfDay();
                $year_middle_last = $year_middle_first->copy()->addDays(1);
                $year_end = $date_start->copy()->endOfYear()->startOfDay();
                if ($date_start->between($year_start, $year_middle_first)) {
                    $date_end = $year_middle_first;
                } else if ($date_start->between($year_middle_last, $year_end)) {
                    $date_end = $year_middle_last->addYears(1)->subDay();
                }
                $data['date_end'] = $date_end;

                // Check if Banding records exists
                if ($associate->bandings_gi()->exists()) {
                    // Get all banding records (ordered by date_end)
                    $bandings = $associate->bandings_gi->sortBy('date_end');
                    $earliest_banding = $bandings->first();
                    $latest_banding = $bandings->last();

                    // Compare if new record is at the start of end of the spectrum
                    if ($earliest_banding->date_start->lte($date_end) && $earliest_banding->date_end->gt($date_end)) {
                        // New record will replace the earliest record
                        // Amend the current earliest record
                        $earliest_banding->date_start = $date_end->copy()->addDays(1);
                        $earliest_banding->save();
                    } else if ($latest_banding->date_start->lt($date_start) && $latest_banding->date_end->gte($date_start)) {
                        // New record will replace the latest record
                        // Amend the current last record
                        $latest_banding->date_end = $date_start->copy()->subDays(1);
                        $latest_banding->save();
                    } else if (
                        ($earliest_banding->date_start->gte($date_start) && $earliest_banding->date_end->lte($date_end)) ||
                        ($latest_banding->date_start->gte($date_start) && $latest_banding->date_end->lte($date_end))) {
                        return Common::reject(403, 'bandings_overlap');
                    }
                }

                // Create new banding record
                $banding = $associate->bandings_gi()->create($data);
                // $associate->log(auth()->user(), 'banding_gi_created', 'Created new GI banding record.', null, $banding->fresh());

                return response()->json([
                    'message' => 'banding_created',
                    'error' => false,
                    'data' => self::index($associate->fresh())
                ]);

                break;

            case 'providers_codes':
                // Retrieve associate
                $associate = Associate::firstUuid($model_uuid);

                // Create new banding record
                $provider_code = $associate->providers_codes()->create($data);
                // $associate->log(auth()->user(), 'provider_codes_created', 'Created new provider_code record.', null, $provider_code->fresh());

                return response()->json([
                    'message' => 'provider_code_created',
                    'error' => false,
                    'data' => self::index($associate->fresh())
                ]);
                break;

            default:
                $user = User::firstUuid($model_uuid);
                // Create a new Associate record
                $associate = Associate::create($data);
                $user->sales_associate()->associate($associate)->save();

                return $associate;
        } // end switch
    }



    /** ===================================================================================================
    * Map data record
    **/
    public static function map($data, $type = null)
    {
        switch ($type) {
            case 'providers_codes':
                // return $data->groupBy('provider_slug')->map(function ($item) { return $item->pluck('code'); });\
                return $data;
                break;

            case 'movements':
                return $data->map(function ($item) {
                    return [
                        'uuid' => $item->uuid,
                        // 'designation' => $item->designation,
                        'designation_slug' => $item->designation_slug,
                        'reporting_uuid' => $item->reporting_uuid,
                        'aa_code' => $item->aa_code,
                        'lfa_code' => $item->lfa_code,
                        'date_start' => $item->date_start,
                        'date_end' => $item->date_end,
                    ];
                });
                break;

            case 'bandings_lfa':
                return $data->map(function ($item) {
                    return [
                        'uuid' => $item->uuid,
                        'banding_type' => $item->banding_type,
                        'rank' => $item->rank,
                        'rate' => $item->rate,
                        'date_start' => $item->date_start,
                        'date_end' => $item->date_end,
                    ];
                });
                break;

            case 'bandings_gi':
                return $data->map(function ($item) {
                    return [
                        'uuid' => $item->uuid,
                        'rank' => $item->rank,
                        'date_start' => $item->date_start,
                        'date_end' => $item->date_end,
                    ];
                });
                break;
        } // end switch
    }
}