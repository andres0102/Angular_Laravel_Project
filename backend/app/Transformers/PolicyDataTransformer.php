<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\DB;

use App\Helpers\{Common, PolicyHelper};

class PolicyDataTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($policy)
    {
        $thumbnail_png = 'providers/logos/' . $policy->provider_alias . '.png';
        $thumbnail_1x = 'providers/logos/' . $policy->provider_alias . '.jpg';
        $thumbnail_2x = 'providers/logos/' . $policy->provider_alias . '@2x.jpg';

        return [
            'uuid' => $policy->uuid,
            'commissions' => $policy->commissions,
            'provider' => [
                'name' => $policy->provider_name,
                'alias' => $policy->provider_alias,
                'color' => $policy->provider_color,
                'background' => $policy->provider_background,
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
                'name' => $policy->client_name
            ],
            'life_assured' => [
                'uuid' => $policy->life_assured_uuid,
                'name' => $policy->life_assured_name
            ],
            'la_is_client' => (boolean) $policy->la_is_client,
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
