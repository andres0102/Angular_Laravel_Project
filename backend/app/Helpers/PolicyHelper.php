<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Transformers\{Data_PolicyTransformer, Data_PolicyTransactionTransformer};

class PolicyHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($policy = null, $associate = null, $client = null)
    {
        $la_individual_query = "(SELECT JSON_OBJECT(
                            'full_name', full_name,
                            'gender_slug', gender_slug,
                            'job_title', job_title,
                            'company_name', company_name,
                            'date_birth', date_birth
                        ) FROM lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_clients.life_assured WHERE policy_holder_uuid = policy.client_uuid LIMIT 1)) as la_individual";

        $client_individual_query = "(SELECT JSON_OBJECT(
                            'full_name', full_name,
                            'gender_slug', gender_slug,
                            'job_title', job_title,
                            'company_name', company_name,
                            'date_birth', date_birth
                        ) FROM lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_clients.clients WHERE uuid = policy.client_uuid LIMIT 1)) as client_individual";

        $client_query = "(SELECT JSON_OBJECT(
                            'client_type_slug', client_type_slug,
                            'description', description,
                            'business_name', business_name,
                            'business_uen', business_uen,
                            'display_name', display_name
                        ) FROM lfa_clients.clients WHERE uuid = policy.client_uuid LIMIT 1) as client_record";


        $provider_query = "(SELECT JSON_OBJECT(
                            'full_name', full_name,
                            'alias', alias,
                            'color', color,
                            'background', background
                        ) FROM lfa_selections._lfa_providers WHERE slug = policy.provider_slug LIMIT 1) as provider";

        $query = DB::connection('lfa_policies')
                    ->table('policies as policy')
                    ->where('policy.deleted_at', null)
                    ->select(
                        'policy.*',
                        DB::raw($provider_query),
                        DB::raw("(SELECT full_name from lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_users.users WHERE associate_uuid = policy.associate_uuid)) as associate_name"),
                        DB::raw($client_query),
                        DB::raw($client_individual_query),
                        DB::raw($la_individual_query),
                        // DB::raw("(SELECT full_name from lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_clients.clients WHERE uuid = policy.client_uuid)) as client_name"),
                        // DB::raw("(SELECT full_name from lfa_individuals.individuals WHERE uuid = (SELECT individual_uuid FROM lfa_clients.life_assured WHERE uuid = policy.life_assured_uuid)) as life_assured_name"),
                        DB::raw("(SELECT SUM(amount) FROM lfa_payroll.computations WHERE computations.policy_uuid = policy.uuid AND computations.payee_uuid = policy.associate_uuid AND computations.firm_revenue = false) as commissions")
                    )->orderByDesc('policy.date_inception');

        if ($policy) {
            $results = $query->where('policy.uuid', $policy->uuid);
            return fractal($results->first(), new Data_PolicyTransformer())->toArray()['data'];
        } else {
            if ($associate) {
                $results_assoc = $query->where('policy.associate_uuid', $associate->uuid);
            } else {
                $results_assoc = $query;
            }

            if ($client) {
                $results = $results_assoc->where('policy.client_uuid', $client->uuid);
            } else {
                $results = $results_assoc;
            }

            return fractal($results->get()->toArray(), new Data_PolicyTransformer())->toArray()['data'];
        }
    }

    /** ===================================================================================================
    * Policy Transactions
    **/
    public static function transactions($policy)
    {
        $query = DB::connection('lfa_policies')
                    ->table('transactions as transaction')
                    ->where('transaction.deleted_at', null)
                    ->where('transaction.policy_uuid', $policy->uuid)
                    ->orderByDesc('transaction.created_at');

        return fractal($query->get()->toArray(), new Data_PolicyTransactionTransformer())->toArray()['data'];
    }




    /** ===================================================================================================
    * Map data record
    **/
    public static function map($data, $type = null)
    {
        switch ($type) {
            case 'transactions':
                return $data->map(function ($item) {
                    return [
                        'uuid' => $item->uuid,
                        'year' => $item->year,
                        'month' => $item->month,
                        'transaction_no' => $item->transaction_no,
                        'transaction_code' => $item->transaction_code,
                        'transaction_desc' => $item->transaction_desc,
                        'date_transaction' => $item->date_transaction,
                        'date_instalment_from' => $item->date_instalment_from,
                        'date_instalment_to' => $item->date_instalment_to,
                        'date_due' => $item->date_due,
                        'product_code' => $item->product_code,
                        'product_type' => $item->product_type,
                        'product_name' => $item->product_name,
                        'component_code' => $item->component_code,
                        'component_name' => $item->component_name,
                        'payment_currency' => $item->payment_currency,
                        'premium' => $item->premium,
                        'premium_gst' => $item->premium_gst,
                        'premium_loading' => $item->premium_loading,
                        'premium_conversion_rate' => $item->premium_conversion_rate,
                        'premium_type' => $item->premium_type,
                    ];
                });
                break;
        } // end switch
    }
}