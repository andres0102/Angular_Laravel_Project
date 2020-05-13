<?php
namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\Users\{User, UserPermission};
use App\Models\Media\Media;

class Common
{
    /** ===================================================================================================
     * Mask a sentence for security purposes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function mask($string)
    {
        $collection = explode(' ', $string);
        foreach($collection as $key => $value) {
            if ($key == 0 && !is_numeric($value)) continue;
            if (is_numeric($value)) {
                $number = number_format($value, 2, '.', ',');
                $collection[$key] = substr($number, 0, 1) . preg_replace("/\d/", "*",substr($number, 1));
            } else {
                $collection[$key] = substr($value, 0, 1) . str_repeat("*", strlen($value)-1);
            }
        }
        return implode(' ',$collection);;
    }

    public static function trimString($string = "") {
        $transformed = preg_replace('/\s+/', ' ', trim($string));
        return (blank($transformed)) ? null : $transformed;
    }

    public static function trimStringUpper($string = "") {
        $transformed = self::trimString($string);
        return (blank($transformed)) ? null : strtoupper($transformed);
    }

    public static function validData($data, $child = null) {
        if ($child) {
            return isset($data[$child]) && !blank($data[$child]);
        } else {
            return isset($data) && !blank($data);
        }
    }

    public static function dataOrNull($data, $child = null) {
        if ($child) {
            return (self::validData($data, $child)) ? $data[$child] : null;
        } else {
            return (self::validData($data)) ? $data : null;
        }

    }

    public static function validDate($data) {
        return isset($data) && ((bool)strtotime($data) ||
                                (strlen($data) === 10 && checkdate((int) substr($data, 3, 2), (int) substr($data, 0, 2), (int) substr($data, -4, 4))) ||
                                (strlen($data) === 8 && checkdate((int) substr($data, 4, 2), (int) substr($data, -2, 2), (int) substr($data, 0, 4))) );
    }

    public static function parseDate($data, $child, $format) {
        if (self::validData($data, $child) && self::validDate($data[$child])){
            return Carbon::createFromFormat($format, $data[$child]) ?? null;
        } else {
            return null;
        }
    }

    public static function ytdMonthsArray($month) {
        $months_array = [];
        for ($i = 1; $i <= (int)$month; $i++) {
            array_push($months_array, (string)str_pad((string)$i, 2, "0", STR_PAD_LEFT));
        }
        return $months_array;
    }

    public static function monthsArray() {
        return array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    }

    public static function pageCollection($collection)
    {
        $page = Paginator::resolveCurrentPage('page');
        $page_size = env('RESULTS_PER_PAGE');
        $total = $collection->count();
        return $collection->forPage($page, $page_size);
    }

    public static function pageMeta($collection, $count)
    {
        $page = Paginator::resolveCurrentPage('page');
        $page_size = env('RESULTS_PER_PAGE');
        $total = $collection->count();
        $total_pages = ceil($total/$page_size);
        $url = Paginator::resolveCurrentPath();
        $url_next = ($page < $total_pages && $total_pages > 1) ? $url . "?page=" . ($page + 1) : null;
        $url_previous = ($page > 1 && $total_pages != 1 && $page < $total_pages) ? $url . "?page=" . ($page - 1) : null;
        $url_last = ($total_pages > 1) ? $url . "?page=" . $total_pages : null;

        return [
            "meta" => [
                "pagination" => [
                    "total" => (int) $total,
                    "count" => (int) $count,
                    "per_page" => (int) $page_size,
                    "current_page" => (int) $page,
                    "total_pages" => (int) $total_pages,
                    "links" => [
                        "first" => $url,
                        "previous" => $url_previous,
                        "next" => $url_next,
                        "last" => $url_last
                    ]
                ]
            ]
        ];
    }

    public static function id_type($identity_no = null) {
        if (!$identity_no) return null;

        $first_char = strtolower(substr($identity_no, 1));
        switch ($first_char) {
            case 's': $identity_type = 'nric_no'; break;
            case 't': $identity_type = 'nric_no'; break;
            case 'f': $identity_type = 'fin_no'; break;
            case 'g': $identity_type = 'fin_no'; break;
            default: $identity_type = 'passport_no';
        }

        return $identity_type;
    }

    public static function reject($code, $message)
    {
        return request()->expectsJson()
            ? response()->json(['error' => true, 'message' => $message, 'code' => $code], $code)
            : abort($code, $message);
    }

    public static function lfa_individual_data($model)
    {
        return [
            'personal' => ($model) ? [
                'uuid' => $model['individual_uuid'],
                'salutation_slug' => $model['salutation_slug'],
                'full_name' => strtoupper($model['full_name']),
                'alias' => self::dataOrNull($model['alias']),
                'chinese_name' => self::dataOrNull($model['chinese_name']),
                'nric_no' => $model['nric_no'],
                'fin_no' => $model['fin_no'],
                'passport_no' => $model['passport_no'],
                'gender_slug' => $model['gender_slug'],
                'date_birth' => $model['date_birth'],
                'race_slug' => $model['race_slug'],
                'country_birth_slug' => $model['country_birth_slug'],
                'nationality_slug' => $model['nationality_slug'],
                'residency_status_slug' => $model['residency_status_slug'],
                'marital_status_slug' => $model['marital_status_slug'],
                'employment_status_slug' => $model['employment_status_slug'],
                'income_range' => $model['income_range'],
                'job_title' => $model['job_title'],
                'company_name' => $model['company_name'],
                'business_nature' => $model['business_nature'],
                'education_level_slug' => $model['education_level_slug'],
                'education_institution' => $model['education_institution'],
                'field_of_study' => $model['field_of_study'],
                'smoker' => (boolean) $model['smoker'],
                'selected' => (boolean) $model['selected'],
                'pdpa' => (boolean) $model['pdpa'],
                'contact_information' => [
                    'email' => $model['contact_information']['email'] ?? null,
                    'home_no' => $model['contact_information']['home_no'] ?? null,
                    'mobile_no' => $model['contact_information']['mobile_no'] ?? null,
                    'fax_no' => $model['contact_information']['fax_no'] ?? null
                ],
                'address_information' => [
                    'block' => $model['address_information']['block'] ?? null,
                    'street' => $model['address_information']['street'] ?? null,
                    'unit' => $model['address_information']['unit'] ?? null,
                    'building' => $model['address_information']['building'] ?? null,
                    'city' => $model['address_information']['city'] ?? null,
                    'postal' => $model['address_information']['postal'] ?? null,
                    'country_slug' => $model['address_information']['country_slug'] ?? null,
                ],
            ] : null
        ];
    }

    /*
    public static function lfa_client_data($model)
    {
        return array_merge([
            'uuid' => $model->uuid,
            'associate' => [
                'uuid' => $model->associate_uuid,
                'name' => $model->associate_name,
            ],
            'lead_stage' => $model->lead_stage,
            'client_type' => $model->client_type,
            'source' => $model->client_source,
            'submissions_count' => (int) $model->submissions_count,
            'policies_count' => (int) $model->policies_count,
            'activities_count' => (int) $model->activities_count,
            'aliases_names' => $model->aliases_names,
            'display_photo' => [
                'original' => ($model->profile) ? Media::whereId($model->profile)->first()->getFullUrl() : null,
                'thumbnail' => ($model->profile) ? Media::whereId($model->profile)->first()->getFullUrl('thumbnail') : null,
            ],
        ], self::lfa_individual_data($model));
    }
    */

    public static function lfa_staff_data($model)
    {
        $devices = collect(DB::connection('lfa_users')->table('devices')->where('user_uuid', '=', $model->user_uuid)->get()->toArray());
        $user = User::firstUuid($model->user_uuid);
        $user_is_admin = ($user->hasRole('super-admin') || $user->hasRole('admin'));
        $user_is_manager = ($model->is_associate && ($model->tier == 2 || $model->tier == 3)) ? true : false;

        return array_merge(self::lfa_individual_data(json_decode($model->individual, true)), [
            'spouse' => [
                'associate_uuid' => self::dataOrNull($model->spouse_associate_uuid),
                'name' => strtoupper(self::dataOrNull($model->spouse_name)),
                'job_title' => self::dataOrNull($model->spouse_job_title),
                'company_name' => self::dataOrNull($model->spouse_company_name),
            ],
            'user_role' => $user->roles->first()->name ?? null,
            'permissions' => ($user->hasRole('super-admin')) ? UserPermission::all()->pluck('name') : $user->getAllPermissions()->pluck('name'),
            'email' => self::dataOrNull(strtolower($model->lfa_email)),
            'activated' => (boolean) $model->activated,
            'setup' => (boolean) $model->setup,
            'private' => (boolean) $model->private,
            'roles' => [
                'is_admin' => (boolean) $user_is_admin,
                'is_manager' => (boolean) $user_is_manager,
                'is_associate' => (boolean) $model->is_associate,
                'is_staff' => (boolean) $model->is_staff,
                'is_assistant' => (boolean) $model->is_assistant,
                'is_candidate' => (boolean) $model->is_candidate,
                'is_client' => (boolean) $model->is_client,
                'is_guest' => (boolean) $model->is_guest,
            ],
            'display_photo' => [
                'original' => ($model->profile) ? Media::whereId($model->profile)->first()->getFullUrl() : null,
                'thumbnail' => ($model->profile) ? Media::whereId($model->profile)->first()->getFullUrl('thumbnail') : null,
            ],
            'lfa' => [
                'uuid' => $model->uuid,
                'onboarding_status' => $model->onboarding_status,
                'onboarding_status_slug' => $model->onboarding_status_slug,
                'designation' => $model->designation,
                'designation_slug' => $model->designation_slug,
                'printer_id' => self::dataOrNull($model->lfa_printer_id),
                'did_no' => self::dataOrNull($model->lfa_did_no),
                'devices' => $devices,
                'dates' => [
                    'date_lfa_application' => $model->date_lfa_application,
                    'date_ceo_interview' => $model->date_ceo_interview,
                    'date_contract_start' => $model->date_contract_start,
                    'date_onboarded' => $model->date_onboarded,
                    'date_offboarded' => $model->date_offboarded,
                    'date_resigned' => $model->date_resigned,
                    'date_last_day' => $model->date_last_day,
                ],
            ]
        ]);
    }













/*
    public static function lfa_salesforce_data($model)
    {
        $associate_array = self::lfa_staff_data($model);
        $providers_codes = collect(DB::connection('lfa_associates')->table('providers_codes')->where('associate_uuid', '=', $model->uuid)->get()->toArray());
        $banding_lfa = collect(DB::connection('lfa_associates')->table('bandings_lfa')->where('associate_uuid', '=', $model->uuid)->orderBy('date_end')->get()->toArray());
        $banding_gi = collect(DB::connection('lfa_associates')->table('bandings_gi')->where('associate_uuid', '=', $model->uuid)->orderBy('date_end')->get()->toArray());
        $movements = collect(DB::connection('lfa_associates')->table('movements')
                            ->leftJoin('lfa_selections._lfa_designations', 'movements.designation_slug', '=', '_lfa_designations.slug')
                            ->where('associate_uuid', '=', $model->uuid)->orderBy('date_end')
                            ->select(
                                'movements.*',
                                DB::raw('if(_lfa_designations.title = "", null, _lfa_designations.title) as designation'))
                            ->orderBy('date_end')->get()->toArray());


        $associate_array['lfa']['salesforce'] = [
            'active' => (boolean) $model->active,
            'designation' => $model->sales_designation,
            'first_day' => $model->first_day,
            'last_day' => $model->last_day,
            'applicant_code' => Common::dataOrNull(strtoupper($model->applicant_code)),
            'lfa_code' => Common::dataOrNull(strtoupper($model->lfa_code)),
            'is_salesforce' => (boolean) $model->is_salesforce,
            'is_overriding' => (boolean) $model->is_overriding,
            'is_t1' => (boolean) $model->is_t1,
            'is_t2' => (boolean) $model->is_t2,
            'is_t3' => (boolean) $model->is_t3,
            'count' => [
                'clients' => (int) $model->clients_count,
                'policies' => (int) $model->policies_count,
                'submissions' => (int) $model->submissions_count,
            ],
            'rnf' => [
                'status' => $model->rnf_status,
                'no' => Common::dataOrNull($model->lfa_rnf_no),
                'date_submission' => $model->date_rnf_submission,
                'date_approval' => $model->date_rnf_approval,
                'date_withdrawal' => $model->date_rnf_withdrawal,
                'date_cessation' => $model->date_rnf_cessation,
            ],
            'certs' => [
                'date_m9' => $model->date_m9,
                'date_m9a' => $model->date_m9a,
                'date_m5' => $model->date_m5,
                'date_hi' => $model->date_hi,
                'date_m8' => $model->date_m8,
                'date_m8a' => $model->date_m8a,
                'date_cert_life_insurance_ilp' => $model->date_cert_ilp,
                'date_cert_life_insurance' => $model->date_cert_li,
                'date_cert_financial_needs_analysis' => $model->date_cert_fna,
                'date_cert_bcp' => $model->date_cert_bcp,
                'date_cert_pgi' => $model->date_cert_pgi,
                'date_cert_comgi' => $model->date_cert_comgi,
                'date_cert_cgi' => $model->date_cert_cgi,
                'professional_certification' => $model->cert_pro,
            ],
            'eligibity' => [
                'eligible_life' => (boolean) $model->lfa_eligible_life,
                'eligible_health' => (boolean) $model->lfa_eligible_health,
                'eligible_ilp' => (boolean) $model->lfa_eligible_ilp,
                'eligible_cis' => (boolean) $model->lfa_eligible_cis,
                'eligible_gi' => (boolean) $model->lfa_eligible_gi,
                'eligible_medishield' => (boolean) $model->lfa_eligible_medishield,
            ],
            'provider_codes' => $providers_codes->groupBy('provider_slug')->map(function ($item) { return $item->pluck('code'); }),
            'movements' => $movements->map(function ($item) {
                return [
                    'uuid' => $item->uuid,
                    'designation' => $item->designation,
                    'designation_slug' => $item->designation_slug,
                    'reporting_uuid' => $item->reporting_uuid,
                    'aa_code' => $item->aa_code,
                    'lfa_code' => $item->lfa_code,
                    'date_start' => $item->date_start,
                    'date_end' => $item->date_end,
                ];
            }),
            'banding_lfa' => $banding_lfa->map(function ($item) {
                return [
                    'uuid' => $item->uuid,
                    'banding_type' => $item->banding_type,
                    'rank' => $item->rank,
                    'rate' => $item->rate,
                    'date_start' => $item->date_start,
                    'date_end' => $item->date_end,
                ];
            }),
            'banding_gi' => $banding_gi->map(function ($item) {
                return [
                    'uuid' => $item->uuid,
                    'rank' => $item->rank,
                    'date_start' => $item->date_start,
                    'date_end' => $item->date_end,
                ];
            }),
        ];

        return $associate_array;
    }
*/
}