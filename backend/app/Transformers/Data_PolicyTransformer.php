<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class Data_PolicyTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($policy)
    {
        $provider = json_decode($policy->provider);

        $thumbnail_png = 'providers/logos/' . $provider->alias . '.png';
        $thumbnail_1x = 'providers/logos/' . $provider->alias . '.jpg';
        $thumbnail_2x = 'providers/logos/' . $provider->alias . '@2x.jpg';

        $client_record = json_decode($policy->client_record);
        $client_individual = json_decode($policy->client_individual);
        $la_individual = json_decode($policy->la_individual);

        return [
            'uuid' => $policy->uuid,
            'commissions' => $policy->commissions,
            'provider' => [
                'name' => $provider->full_name,
                'alias' => $provider->alias,
                'color' => $provider->color,
                'background' => $provider->background,
                'thumbnail_png' => file_exists(storage_path('app/public/'.$thumbnail_png)) ? asset('storage/'.$thumbnail_png) : null,
                'thumbnail_1x' => file_exists(storage_path('app/public/'.$thumbnail_1x)) ? asset('storage/'.$thumbnail_1x) : null,
                'thumbnail_2x' => file_exists(storage_path('app/public/'.$thumbnail_2x)) ? asset('storage/'.$thumbnail_2x) : null,
            ],
            'associate' => [
                'uuid' => $policy->associate_uuid,
                'name' => $policy->associate_name
            ],
            'client' => [
                'uuid' => $policy->client_uuid,
                'client_type_slug' => $client_record->client_type_slug,
                'display_name' => $client_record->display_name,
                'personal' => $client_individual,
                'business_uen' => $client_record->business_uen,
                'description' => $client_record->description,
            ],
            'life_assured' => [
                'uuid' => $policy->life_assured_uuid,
                'personal' => $la_individual
            ],
            'policy_no' => $policy->policy_no,
            'contract_currency' => $policy->contract_currency,
            'policy_term' => $policy->policy_term,
            'premium_term' => $policy->premium_term,
            'payment_frequency' => $policy->payment_frequency,
            'date_issued' => $policy->date_issued,
            'date_inception' => $policy->date_inception,
            'date_expiry' => $policy->date_expiry,
            'sum_assured' => $policy->sum_assured,
            'total_investment' => $policy->total_investment,
        ];
    }
}
