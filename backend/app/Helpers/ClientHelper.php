<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Selections\SelectRace;
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\Client;
use App\Models\Individuals\Individual;
use App\Transformers\{Data_ClientTransformer, Data_ClientLifeAssuredTransformer};

class ClientHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($client = null, $associate = null, $record_type = null, $show_all = false)
    {
        $individual_contact_query = "(SELECT JSON_OBJECT(
                            'home_no', home_no,
                            'mobile_no', mobile_no,
                            'fax_no', fax_no,
                            'email', email
                        ) FROM lfa_individuals.contacts WHERE contacts.individual_uuid = uuid AND contact_type_slug = 'default' LIMIT 1)";

        $individual_address_query = "(SELECT JSON_OBJECT(
                            'block', block,
                            'street', street,
                            'unit', unit,
                            'building', building,
                            'city', city,
                            'postal', postal,
                            'country_slug', country_slug
                        ) FROM lfa_individuals.addresses WHERE addresses.individual_uuid = uuid AND address_type_slug = 'residential' LIMIT 1)";

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
                        ) FROM lfa_individuals.individuals WHERE uuid = client.individual_uuid AND client.individual_uuid IS NOT NULL LIMIT 1) as individual";

        // Introducer Scheme
        $introducer_query = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'uuid', uuid,
                            'year', year,
                            'date_start', date_start,
                            'date_end', date_end,
                            'gift_received', gift_received
                        )) FROM lfa_clients.introducers WHERE client_uuid = client.uuid) as introducer_scheme";

        $query = DB::connection('lfa_clients')
                    ->table('clients as client')
                    // ->leftJoin('lfa__default.media', function($join)
                    //     {
                    //         $join->on('client.id', '=', 'media.model_id');
                    //         $join->on('model_type','=',DB::raw("'clients'"));
                    //         $join->on('collection_name','=',DB::raw("'display_photo'"));
                    //     })
                    ->select(
                        'client.*',
                        // DB::raw('ifnull(media.id, null) as profile'),
                        DB::raw("(SELECT full_name from lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_users.users WHERE associate_uuid = client.associate_uuid)) as associate_name"),
                        DB::raw("(SELECT full_name from lfa_individuals.individuals WHERE uuid = individual_uuid) as name"),
                        // Individual Data
                        DB::raw($individual_query),
                        DB::raw($introducer_query),
                        DB::raw("(SELECT count(*) FROM lfa_policies.policies WHERE client.uuid = policies.client_uuid) as policies_count"),
                        DB::raw("(SELECT count(*) FROM lfa_submissions.submissions WHERE client.uuid = submissions.client_uuid) as submissions_count"),
                        DB::raw("(SELECT count(*) FROM lfa_clients.life_assured WHERE client.uuid = life_assured.policy_holder_uuid) as life_assured_count"),
                        DB::raw("(SELECT count(*) FROM lfa_clients.nominees WHERE client.uuid = nominees.client_uuid) as nominees_count"),
                        DB::raw("(SELECT uuid FROM lfa_clients.nominees WHERE client.uuid = nominees.converted_client_uuid LIMIT 1) as nominee"),
                        // DB::raw("(SELECT count(*) FROM lfa__general.logs WHERE loggable_type = 'clients' AND loggable_id = client.id) as activities_count"),
                        DB::raw("(SELECT DISTINCT GROUP_CONCAT(full_name SEPARATOR ', ') FROM lfa_clients.aliases WHERE aliases.client_uuid = client.uuid) as aliases_names")
                    )->orderBy('display_name');


        if ($client) {
            if ($record_type == 'nominee') {
                $query = $query->whereRaw('client.uuid in (SELECT converted_client_uuid from lfa_clients.nominees where nominees.client_uuid = "'. $client->uuid .'")');
                return fractal($query->get()->toArray(), new Data_ClientTransformer())->toArray()['data'];
            } else {
                $results = $query->where('client.uuid', $client->uuid);
                return fractal($results->first(), new Data_ClientTransformer())->toArray()['data'];
            }
        } else {
            if ($record_type == 'client') {
                $query = $query->where('client.is_lead', false);
            } else if ($record_type == 'lead') {
                $query = $query->where('client.is_lead', true);
            }

            if (!$show_all) $query = $query->where('client.deleted_at', null);

            if ($associate) {
                $results = $query->where('client.associate_uuid', $associate->uuid);
            } else {
                $results = $query;
            }

            return fractal($results->get()->toArray(), new Data_ClientTransformer())->toArray()['data'];
        }
    }


    /** ===================================================================================================
     * Function to return validations for table fields
     *
     */
    public static function validations($type = null, $required = false)
    {
      $required_str = ($required) ? 'required|' : 'nullable|';

      switch ($type) {
        case 'display_name':
            return [
                'client_type_slug' => 'nullable|string|exists:lfa_selections._lfa_client_types,slug',
                'display_name' => 'nullable|string',
                'nric_no' => 'nullable|string',
                'business_uen' => 'nullable|string',
            ];
            break;
        case 'individual':
            return [
                'full_name' => 'nullable|string',
                'alias' => 'nullable|string',
                'chinese_name' => 'nullable|string',
                'nric_no' => 'nullable|string',
                'fin_no' => 'nullable|string',
                'passport_no' => 'nullable|string',
                'date_birth' => 'nullable|date',
                'salutation_slug' => 'nullable|string|exists:lfa_selections.salutations,slug',
                'gender_slug' => 'nullable|string|exists:lfa_selections.genders,slug',
                'marital_status_slug' => 'nullable|string|exists:lfa_selections.marital_status,slug',
                'race_slug' => 'nullable|string|exists:lfa_selections.race,slug',
                'country_birth_slug' => 'nullable|string|exists:lfa_selections.countries,slug',
                'nationality_slug' => 'nullable|string|exists:lfa_selections.countries,slug',
                'residency_status_slug' => 'nullable|string|exists:lfa_selections.residency_status,slug',
                'employment_status_slug' => 'nullable|string|exists:lfa_selections.employment_status,slug',
                'income_range' => 'nullable|string',
                'job_title' => 'nullable|string',
                'company_name' => 'nullable|string',
                'business_nature' => 'nullable|string',
                'education_level_slug' => 'nullable|string|exists:lfa_selections.educational_levels,slug',
                'education_institution' => 'nullable|string',
                'field_of_study' => 'nullable|string',
                'smoker' => 'nullable|boolean',
                'selected' => 'nullable|boolean',
                'pdpa' => 'nullable|boolean',
            ];
            break;
        case 'individual_contact':
            return [
                'email' => 'nullable|string',
                'home_no' => 'nullable|string',
                'mobile_no' => 'nullable|string',
                'fax_no' => 'nullable|string',
            ];
            break;
        case 'individual_address':
            return [
                'block' => 'nullable|string',
                'street' => 'nullable|string',
                'unit' => 'nullable|string',
                'building' => 'nullable|string',
                'city' => 'nullable|string',
                'postal' => 'nullable|string',
                'country_slug' => 'nullable|string|exists:lfa_selections.countries,slug',
            ];
            break;
        default:
            return [
                'client_type_slug' => 'nullable|string|exists:lfa_selections._lfa_client_types,slug',
                'associate_uuid' => 'nullable|uuid|exists:lfa_associates.associates,uuid',
                'individual_uuid' => 'nullable|uuid|exists:lfa_individuals.individuals,uuid',
                'is_lead' => 'nullable|boolean',
                'sales_stage_slug' => 'nullable|string|exists:lfa_selections._lfa_sales_stage,slug',
                'lead_stage_slug' => 'nullable|string|exists:lfa_selections._lfa_lead_stage,slug',
                'source_slug' => 'nullable|string|exists:lfa_selections._lfa_client_sources,slug',
                'display_name' => $required_str . 'string',
                'business_name' => 'nullable|string',
                'business_uen' => 'nullable|string',
                'description' => 'nullable',
                'interest' => 'nullable',
                'important' => 'nullable',
                'nric_no' => 'nullable',
                'job_title' => 'nullable',
                'company_name' => 'nullable',
                'gender_slug' => 'nullable',
                'date_birth' => 'nullable|date',
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
    * Create Client record
    * via App\Http\Controllers\Admin\AdminAssociatesClientsController
    **/
    public static function create($data, $model = null, $is_lead = false)
    {
        // Create new Client procedure
        $sales_associate = Associate::firstUuid($model->uuid);

        if (request()->input('client_type_slug') == 'individual') {
            // Create Individual Record
            $individual = Individual::create([
                'full_name' => Common::trimStringUpper($data['display_name']),
                'job_title' => $data['job_title'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'gender_slug' => $data['gender_slug'] ?? null,
                'date_birth' => Common::trimString($data['date_birth']) ?? null,
            ]);
            if ($nric_no = Common::trimStringUpper($data['nric_no'])) $individual->update([ Common::id_type($nric_no) => $nric_no ]);

            // Create Client Record
            $client = $sales_associate->clients()->create([
                'is_lead' => $is_lead,
                'client_type_slug' => $data['client_type_slug'],
                'source_slug' => $data['source_slug'],
                'display_name' => Common::trimStringUpper($data['display_name']),
                'individual_uuid' => $individual->uuid,
                'lead_stage_slug' => $data['lead_stage_slug'] ?? (($is_lead) ? 'new' : 'converted-to-client'),
                'sales_stage_slug' => $data['sales_stage_slug'] ?? 'new',
            ]);

            // Create Client Alias Record
            $client->aliases()->create([
                'full_name' => Common::trimStringUpper($data['display_name']),
                'nric_no' => Common::trimStringUpper($data['nric_no'] ?? null),
                'associate_uuid' => $sales_associate->uuid
            ]);

        } else if (request()->input('client_type_slug') == 'business') {
            // Create Client Record
            $client = $sales_associate->clients()->create([
                'is_lead' => false,
                'client_type_slug' => $data['client_type_slug'],
                'source_slug' => $data['source_slug'],
                'display_name' => Common::trimStringUpper($data['display_name']),
                'business_name' => Common::trimStringUpper($data['display_name']),
                'business_uen' => Common::trimStringUpper($data['business_uen'] ?? null)
            ]);

            // Create Client Alias Record
            $client->aliases()->create([
                'full_name' => Common::trimStringUpper($data['display_name']),
                'nric_no' => Common::trimStringUpper($data['business_uen'] ?? null),
                'associate_uuid' => $sales_associate->uuid
            ]);
        }

        $user = auth()->user() ?? null;
        // $client->log($user, 'created', 'Client record created.');
        return self::index($client);
    }


    /** ===================================================================================================
    * Update Client record
    * via App\Http\Controllers\Admin\AdminAssociatesClientsController
    **/
    public static function update($data, $model = null, $type = null)
    {
        switch ($type) {
            case 'display_name':
                if (isset($data['display_name'])) {
                    $model->update(['display_name' => strtoupper($data['display_name'])]);
                }
                $alias = $model->aliases()->firstOrCreate(['associate_uuid' => $model->associate->uuid, 'full_name' => strtoupper($data['display_name'] ?? $model->display_name)]);
                $client_type_slug = $data['client_type_slug'] ?? $model->client_type_slug;
                if ($client_type_slug == 'individual' && isset($data['nric_no'])) $alias->update(['nric_no' => $data['nric_no']]);
                else if ($client_type_slug == 'business' && isset($data['business_uen'])) $alias->update(['nric_no' => $data['business_uen']]);
                break;

            case 'individual_contact':
                // Update Client Record
                foreach(self::fields($type) as $column) {
                    if (Common::validData($data, $column)) {
                        IndividualHelper::updateContact($model, [ $column => $data[$column] ]);
                    }
                }
                break;

            case 'individual_address':
                // Update Client Record
                foreach(self::fields($type) as $column) {
                    if (Common::validData($data, $column)) {
                        IndividualHelper::updateAddress($model, [ $column => $data[$column] ]);
                    }
                }
                break;

            case 'client':
                // Update Client Record
                foreach(['client_type_slug',
                            'associate_uuid',
                            'individual_uuid',
                            'is_lead',
                            'lead_stage_slug',
                            'sales_stage_slug',
                            'source_slug',
                            'display_name',
                            'business_name',
                            'business_uen',
                            'description',
                            'interest',
                            'important'] as $column) {
                    if (Common::validData($data, $column)) {
                        $model->update([ $column => $data[$column] ]);
                        if ($column == 'lead_stage_slug' && $data[$column] == 'converted-to-client') $model->update([ 'is_lead' => false ]);
                        if ($column == 'business_name' && $data[$column] && $model->client_type_slug == 'business') self::update(['display_name' => $data[$column], 'business_uen' => $data['business_uen'] ?? null], $model, 'display_name');

                        if ($column == 'client_type_slug' && $data[$column] == 'individual' && $model->individual) $model->update(['display_name' => $model->individual->full_name]);
                        else if ($column == 'client_type_slug' && $data[$column] == 'business' && $model->business_name) $model->update(['display_name' => $model->business_name]);
                    }
                }
                break;

            default:
                // Update Client Record
                foreach(self::fields($type) as $column) {
                    if (Common::validData($data, $column)) {
                        if ($column == 'race_slug') {
                            $slug = Str::slug($data[$column]);
                            SelectRace::firstOrCreate(['slug' => $slug], ['title' => ucwords($data[$column])]);
                            $model->update([ $column => $slug ]);
                        } else {
                            $model->update([ $column => $data[$column] ]);
                        }
                    }
                }
        } // end switch
    }




    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function life_assured($client)
    {
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
                        ) FROM lfa_individuals.individuals WHERE uuid = la.individual_uuid) as individual";

        $query = DB::connection('lfa_clients')
                    ->table('life_assured as la')
                    ->where('policy_holder_uuid', $client->uuid)
                    ->select(
                        'la.*',
                        DB::raw($individual_query)
                    );

        return fractal($query->get()->toArray(), new Data_ClientLifeAssuredTransformer())->toArray()['data'];
    }
}