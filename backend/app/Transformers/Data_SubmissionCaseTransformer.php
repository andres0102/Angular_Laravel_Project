<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Helpers\{Common, MediaHelper};
use App\Models\Selections\SelectBank;

class Data_SubmissionCaseTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($case)
    {
        $thumbnail_png = 'providers/logos/' . $case->provider_alias . '.png';
        $thumbnail_1x = 'providers/logos/' . $case->provider_alias . '.jpg';
        $thumbnail_2x = 'providers/logos/' . $case->provider_alias . '@2x.jpg';

        return [
            'uuid' => $case->uuid,
            'submission' => [
                'category' => $case->submission_category,
                'category_slug' => $case->submission_cat_slug,
                'mode' => $case->submission_mode,
                'reference_no' => $case->reference_no,
                'currency' => $case->currency,
                'ape' => $case->ape,
                'life_assured' => [
                    'uuid' => $case->life_assured_uuid,
                    'name' => $case->life_assured_name,
                    'is_client' => (boolean) $case->life_assured_is_client,
                    'relationship' => $case->life_assured_relationship,
                    'personal' => Common::lfa_individual_data(json_decode($case->life_assured_personal, true))['personal']
                ],
                'investment_transaction_type' => $case->investment_transaction_type,
                'investment_account_type' => $case->investment_account_type,
                'date_submission' => $case->date_submission
            ],
            'provider' => [
                'full_name' => $case->provider_name,
                'slug'  => $case->provider_slug,
                'alias'  => $case->provider_alias,
                'color'  => $case->provider_color,
                'background'  => $case->provider_background,
                'thumbnail_png' => file_exists(storage_path('app/public/'.$thumbnail_png)) ? asset('storage/'.$thumbnail_png) : null,
                'thumbnail_1x' => file_exists(storage_path('app/public/'.$thumbnail_1x)) ? asset('storage/'.$thumbnail_1x) : null,
                'thumbnail_2x' => file_exists(storage_path('app/public/'.$thumbnail_2x)) ? asset('storage/'.$thumbnail_2x) : null,
            ],
            'product' => [
                'category' => $case->product_category,
                'cat_slug' => $case->product_cat_slug,
                'name' => $case->product_name,
                'product_uuid' => $case->product_uuid,
                'option' => $case->option_name,
                'option_uuid' => $case->product_option_uuid,
            ],
            'riders' => json_decode($case->riders),
            'policy' => [
                'currency' => $case->currency,
                'sum_assured' => (float) $case->sum_assured ?? 0,
                'policy_term' => (int) $case->policy_term ?? 0,
                'payment_type' => $case->payment_type,
                'payment_term' => (int) $case->payment_term ?? 0,
                'payment_mode' => $case->payment_mode,
                'payment_mode_slug' => $case->payment_mode_slug,
                'frequency' => $case->payment_frequency,
                'gst_rate' => (float) $case->gst_rate ?? 0,
                'gross_payment_before_gst' => (float) $case->gross_payment_before_gst ?? 0,
                'gross_payment_gst' => (float) $case->gross_payment_gst ?? 0,
                'gross_payment_after_gst' => (float) $case->gross_payment_after_gst ?? 0,
                'payment_discount' => (float) $case->payment_discount ?? 0,
                'nett_payment_before_gst' => (float) $case->nett_payment_before_gst ?? 0,
                'nett_payment_gst' => (float) $case->nett_payment_gst ?? 0,
                'nett_payment_after_gst' => (float) $case->nett_payment_after_gst ?? 0,
            ],
            'investment' => [
                'transaction_type' => $case->investment_transaction_type,
                'account_type' => $case->investment_account_type,
            ],
            'loan' => [
                'property_address' => $case->loan_property_address,
                'platform' => $case->loan_platform,
                'bank_slug' => $case->loan_bank_slug,
                'bank' => ($case->loan_bank_slug) ? SelectBank::where('slug', $case->loan_bank_slug)->first()->full_name : null,
                'interest_rate' => $case->loan_interest_rate,
                'amount' => $case->loan_amount ?? 0,
            ],
            'media' => MediaHelper::index('submissions_cases', $case),
            'documents' => collect(json_decode($case->documents))
                            ->mapWithKeys(function($item) {
                                return [$item->title => $item->value];
                            })->filter(function ($value, $key) {
                                return in_array($key, [
                                    'doc_pfr_original',
                                    'doc_app_original',
                                    'doc_nric_original',
                                    'doc_bi_original',
                                    'doc_supporting_original',
                                    'doc_others_original',
                                    'doc_consent_original',
                                ]);
                            })
        ];
    }
}
