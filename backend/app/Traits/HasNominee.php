<?php

namespace App\Traits;

use App\Helpers\{Common, IndividualHelper};
use App\Models\Selections\LegacyFA\SelectClientType;
use App\Models\LegacyFA\Clients\{Nominee};
use App\Models\Individuals\Individual;

trait HasNominee
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function nominees() { return $this->hasMany(Nominee::class, 'introducer_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function findNominee($check_name, $check_nric = null) {
        // Check if Client's Details match any one of the many clients' aliases...
        $search = $this->nominees->filter(function($nominee) use ($check_name) {
            return Common::trimStringUpper($nominee->individual->full_name) == Common::trimStringUpper($check_name);
        });

        if ($search->isNotEmpty()) {
            if ($check_nric) {
                $search_nric = $search->filter(function($cnric) use ($check_nric) {
                    return Common::trimStringUpper($cnric->individual->nric_no) == Common::trimStringUpper($check_nric);
                });
                if ($search_nric->isNotEmpty()) {
                    // Exact match :: Name & Nric
                    return $search_nric->first();
                } else {
                    // Client with name match exists, but nric is null
                    // Lets assume that search_client (w/ nric) == client record since full name matches under associate.
                    // Update client with $check_nric value
                    $nominee = $search->first();
                    $nominee->individual->update(['nric_no' => $check_nric]);

                    return $nominee->fresh();
                }
            } else {
                return $search->first();
            }
        } else {
            return null;
        }
    }


    public function findOrNewNominee($check_name, $check_id = null) {
        if (!$nominee = $this->findNominee($check_name, $check_id)) {
            $display_name = Common::trimStringUpper($check_name);
            $identity_no = Common::trimStringUpper($check_id) ?? null;

            if ($check_individual = IndividualHelper::check($display_name, $identity_no)) {
                // Individual Data exists, lets check if it belongs to any other clients
                if (Nominee::where('individual_uuid', $check_individual->uuid)->where('introducer_uuid', '<>', $this->uuid)->count()) {
                    // Individual exists, but belong to other model
                    // Lets create new individual record
                    $individual = IndividualHelper::create([
                        'full_name' => $display_name,
                        'nric_no' => $identity_no ?? null,
                    ]);
                } else {
                    // Individual exists, but does not belongs to other associate
                    $individual = $check_individual;
                }
            } else {
                // Individual does not exists
                // Lets create a new indivdual
                $individual = IndividualHelper::create([
                    'full_name' => $display_name,
                    'nric_no' => $identity_no ?? null,
                ]);
            }

            $client = $this->sales_associate->clients()->create([
                'client_type_slug' => 'individual',
                'display_name' => $display_name,
                'is_lead' => true,
                'lead_stage_slug' => 'new',
                'sales_stage_slug' => 'new',
                'individual_uuid' => $individual->uuid,
                'description' => 'Nominee of ' . Common::trimStringUpper($this->name),
                'source_slug' => 'introducer'
            ]);

            $client->aliases()->create([
                'full_name' => $display_name,
                'nric_no' => $identity_no ?? null,
                'associate_uuid' => $this->sales_associate->uuid
            ]);

            $nominee = $this->nominees()->create([
                'individual_uuid' => $individual->uuid,
                'associate_uuid' => $this->associate_uuid,
                'client_uuid' => $this->client_uuid,
                'converted' => false,
                'converted_client_uuid' => $client->uuid
            ]);
        }
        return $nominee->fresh();
    }
}
