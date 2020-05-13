<?php

namespace App\Traits;

use App\Helpers\{Common, IndividualHelper};
use App\Models\LegacyFA\Clients\{Client, LifeAssured};
use App\Models\Individuals\Individual;

trait HasLifeAssured
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function life_assured() { return $this->hasMany(LifeAssured::class, 'policy_holder_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function findAlias($check_name, $check_nric = null) {
        // Check if Details match any one of the many clients' aliases...
        $search = $this->aliases->filter(function($ca) use ($check_name) {
            return Common::trimStringUpper($ca->full_name) == Common::trimStringUpper($check_name);
        });

        if ($search->isNotEmpty()) {
            if ($check_nric) {
                $search_nric = $search->filter(function($cnric) use ($check_nric) {
                    return Common::trimStringUpper($cnric->nric_no) == Common::trimStringUpper($check_nric);
                });
                if ($search_nric->isNotEmpty()) return $search_nric->first();
            }
            return $search->first();
        } else {
            return null;
        }
    }

    public function findLifeAssured($check_name, $check_nric = null) {
        // Check if Client's Details match any one of the many clients' aliases...
        $search = $this->life_assured()->whereHas('individual', function($q) use ($check_name) {
            $q->where('full_name', Common::trimStringUpper($check_name));
        });

        if ($search->exists()) {
            if ($check_nric) {
                $search_nric = $search->whereHas('individual', function($q) use ($check_nric) {
                    $q->where('nric_no', Common::trimStringUpper($check_nric));
                });
                if ($search_nric->exists()) return $search_nric->first();
            }
            return $search->first();
        } else {
            return null;
        }
    }


    public function findOrNewLifeAssured($check_name, $check_nric = null) {
        if (!$life_assured = $this->findLifeAssured($check_name, $check_nric)) {
            // Life assured currently does not exists...
            // Check if Life Assured's Details match any one of the many Clients' Aliases...
            // See if we shall create a new individual record for this life assured...
            // Or shall we tag the life assured as the client's alias's individual record...
            if ($search_aliases = $this->findAlias($check_name, $check_nric)) {
                // Life Assured's Details match one of the many Clients' Aliases
                // Tag this life assured as the client
                // $life_assured_is_client = true;
                // $individual = $search_aliases->client->individual;
                return $search_aliases->client;
            } else {
                // Life Assured's Details does not match any one of the many Clients' Aliases
                // Create as new separate individual
                // $life_assured_is_client = false;

                if ($check_individual = IndividualHelper::check($check_name, $check_nric)) {
                    // Individual Data exists, lets check if it belongs to any other clients
                    if (Client::where('individual_uuid', $check_individual->uuid)->where('associate_uuid', '<>', $this->uuid)->count()) {
                        // Individual exists, but belong to other associate
                        // Lets create new individual record
                        $individual = IndividualHelper::create([
                            'full_name' => Common::trimStringUpper($check_name),
                            'nric_no' => Common::trimStringUpper($check_nric) ?? null,
                        ]);
                    } else {
                        // Individual exists, but does not belongs to other associate
                        $individual = $check_individual;
                    }
                } else {
                    // Individual does not exists
                    // Lets create a new indivdual
                    $individual = IndividualHelper::create([
                        'full_name' => Common::trimStringUpper($check_name),
                        'nric_no' => Common::trimStringUpper($check_nric) ?? null,
                    ]);
                }
            }

            $client = $this->associate->clients()->create([
                'client_type_slug' => 'individual',
                'display_name' => Common::trimStringUpper($check_name),
                'is_lead' => true,
                'lead_stage_slug' => 'new',
                'sales_stage_slug' => 'new',
                'individual_uuid' => $individual->uuid,
                'description' => 'Life Assured of ' . Common::trimStringUpper($this->name),
                'source_slug' => 'client'
            ]);

            $client->aliases()->create([
                'full_name' => Common::trimStringUpper($check_name),
                'nric_no' => Common::trimStringUpper($check_nric) ?? null,
                'associate_uuid' => $this->associate->uuid
            ]);

            $life_assured = $this->life_assured()->create([
                'associate_uuid' => $this->associate_uuid,
                'individual_uuid' => $individual->uuid
            ]);
        }

        return $life_assured;
    }
}
