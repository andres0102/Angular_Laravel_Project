<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Helpers\{Common, AssociateHelper};

class Data_AssociateTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($associate)
    {
        return array_merge([
            'uuid' => $associate->uuid,
            'active' => (boolean) $associate->active,
            'designation' => $associate->sales_designation,
            'first_day' => $associate->first_day,
            'last_day' => $associate->last_day,
            'applicant_code' => Common::dataOrNull(strtoupper($associate->applicant_code)),
            'lfa_sl_no' => $associate->lfa_sl_no ?? null,
            'lfa_code' => Common::dataOrNull(strtoupper($associate->lfa_code)),
            'is_salesforce' => (boolean) $associate->is_salesforce,
            'is_overriding' => (boolean) $associate->is_overriding,
            'is_t1' => (boolean) $associate->is_t1,
            'is_t2' => (boolean) $associate->is_t2,
            'is_t3' => (boolean) $associate->is_t3,
            'count' => [
                'clients' => (int) $associate->clients_count,
                'policies' => (int) $associate->policies_count,
                'submissions' => (int) $associate->submissions_count,
            ],
            'rnf' => [
                'status' => $associate->rnf_status,
                'status_slug' => $associate->rnf_status_slug,
                'no' => Common::dataOrNull($associate->rnf_no),
                'date_submission' => $associate->date_rnf_submission,
                'date_approval' => $associate->date_rnf_approval,
                'date_withdrawal' => $associate->date_rnf_withdrawal,
                'date_cessation' => $associate->date_rnf_cessation,
            ],
            'certs' => [
                'date_m9' => $associate->date_m9,
                'date_m9a' => $associate->date_m9a,
                'date_m5' => $associate->date_m5,
                'date_hi' => $associate->date_hi,
                'date_m8' => $associate->date_m8,
                'date_m8a' => $associate->date_m8a,
                'date_cert_life_insurance_ilp' => $associate->date_cert_ilp,
                'date_cert_life_insurance' => $associate->date_cert_li,
                'date_cert_financial_needs_analysis' => $associate->date_cert_fna,
                'date_cert_bcp' => $associate->date_cert_bcp,
                'date_cert_pgi' => $associate->date_cert_pgi,
                'date_cert_comgi' => $associate->date_cert_comgi,
                'date_cert_cgi' => $associate->date_cert_cgi,
                'cert_pro' => $associate->cert_pro,
            ],
            'eligibity' => [
                'eligible_life' => (boolean) $associate->lfa_eligible_life,
                'eligible_health' => (boolean) $associate->lfa_eligible_health,
                'eligible_ilp' => (boolean) $associate->lfa_eligible_ilp,
                'eligible_cis' => (boolean) $associate->lfa_eligible_cis,
                'eligible_gi' => (boolean) $associate->lfa_eligible_gi,
                'eligible_medishield' => (boolean) $associate->lfa_eligible_medishield,
            ],
            'provider_codes' => AssociateHelper::map(collect(json_decode($associate->providers_codes)), 'providers_codes'),
            'movements' => AssociateHelper::map(collect(json_decode($associate->movements)), 'movements'),
            'bandings_lfa' => AssociateHelper::map(collect(json_decode($associate->bandings_lfa)), 'bandings_lfa'),
            'bandings_gi' => AssociateHelper::map(collect(json_decode($associate->bandings_gi)), 'bandings_gi'),
        ], Common::lfa_staff_data($associate));
    }
}
