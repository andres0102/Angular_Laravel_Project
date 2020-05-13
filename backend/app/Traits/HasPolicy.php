<?php

namespace App\Traits;

use App\Models\LegacyFA\Clients\ClientPolicy;

trait HasPolicy
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function policies() { return $this->hasMany(ClientPolicy::class, 'client_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function findOrNewPolicy($provider_slug, $policy_no, $policy_array) {
        if (isset($policy_array['life_assured_name'])) {
            $life_assured_name = $policy_array['life_assured_name'];
            $life_assured_nric = $policy_array['life_assured_nric'] ?? null;
            $life_assured = $this->findOrNewLifeAssured($life_assured_name, $life_assured_nric);
            if ($this->is($life_assured)) $life_assured = null; // Life Assured is Client
        }

        $search_policies = $this->policies()->updateOrCreate([
            'provider_slug' => $provider_slug,
            'policy_no' => $policy_no,
            'associate_uuid' => $this->associate_uuid,
            'client_uuid' => $this->uuid
        ], array_filter($policy_array));

        if (isset($life_assured)) $search_policies->update(['life_assured_uuid' => $life_assured->uuid]);

        return $search_policies;
    }
}
