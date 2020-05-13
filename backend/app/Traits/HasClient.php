<?php

namespace App\Traits;

use App\Helpers\{Common, IndividualHelper};
use App\Models\Selections\LegacyFA\SelectClientType;
use App\Models\LegacyFA\Clients\{Client, ClientAlias};
use App\Models\Individuals\Individual;

trait HasClients
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function clients() { return $this->hasMany(Client::class, 'associate_uuid', 'uuid'); }
    public function clients_aliases() { return $this->hasMany(ClientAlias::class, 'associate_uuid', 'uuid'); }
    public function clients_life_assured() { return $this->hasMany(LifeAssured::class, 'associate_uuid', 'uuid'); }
    public function nominees() { return $this->hasMany(Nominee::class, 'associate_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function findClient($check_name, $check_id = null) {
        // Check if Client's Details match any one of the many clients' aliases...
        $search = $this->clients_aliases->filter(function($ca) use ($check_name) {
            return Common::trimStringUpper($ca->full_name) == Common::trimStringUpper($check_name);
        });

        if ($search->isNotEmpty()) {
            if ($check_id) {
                $search_id = $search->filter(function($cnric) use ($check_id) {
                    return Common::trimStringUpper($cnric->nric_no) == Common::trimStringUpper($check_id);
                });
                if ($search_id->isNotEmpty()) {
                    // Exact match :: Name & Nric
                    return $search_id->first()->client;
                } else {
                    // Client with name match exists, but nric is null
                    // Lets assume that search_client (w/ nric) == client record since full name matches under associate.
                    // Update client with $check_id value
                    $alias = $search->first();
                    $alias->update(['nric_no' => $check_id]);

                    $client = $alias->client;
                    $individual = $client->individual;
                    $individual->update([Common::id_type($check_id) => $check_id]);

                    return $client->fresh();
                }
            } else {
                $alias = $search->first();
                return $alias->client;
            }
        } else {
            return null;
        }
    }


    public function findOrNewClient($check_name, $check_id = null, $client_type = 'individual', $is_lead = false) {
        if (!$client = $this->findClient($check_name, $check_id)) {
            $display_name = Common::trimStringUpper($check_name);
            $identity_no = Common::trimStringUpper($check_id) ?? null;

            if ($client_type == 'individual') {
                if ($check_individual = IndividualHelper::check($display_name, $identity_no)) {
                    // Individual Data exists, lets check if it belongs to any other clients
                    if (Client::where('individual_uuid', $check_individual->uuid)->where('associate_uuid', '<>', $this->uuid)->count()) {
                        // Individual exists, but belong to other associate
                        // Lets create new individual record
                        $individual = IndividualHelper::create(['full_name' => $display_name]);
                        if ($identity_no) $individual->update([ Common::id_type($identity_no) => $identity_no ]);
                    } else {
                        // Individual exists, but does not belongs to other associate
                        $individual = $check_individual;
                    }
                } else {
                    // Individual does not exists
                    // Lets create a new indivdual
                    $individual = IndividualHelper::create(['full_name' => $display_name]);
                    if ($identity_no) $individual->update([ Common::id_type($identity_no) => $identity_no ]);
                }

                // Create client record
                $client = $this->clients()->create([
                    'individual_uuid' => $individual->uuid,
                    'client_type_slug' => $client_type,
                    'display_name' => $display_name,
                    'lead_stage_slug' => 'converted-to-client',
                    'sales_stage_slug' => 'new',
                    'is_lead' => $is_lead
                ]);

                // Create client alias
                $client->aliases()->create([
                    'full_name' => $display_name,
                    'nric_no' => $identity_no,
                    'associate_uuid' => $this->uuid
                ]);

            } else if ($client_type == 'business') {

                // Create client record
                $client = $this->clients()->create([
                    'client_type_slug' => $client_type,
                    'display_name' => $display_name,
                    'business_name' => $display_name,
                    'business_uen' => $display_name,
                    'lead_stage_slug' => 'converted-to-client',
                    'sales_stage_slug' => 'new',
                    'is_lead' => $is_lead
                ]);

                // Create client alias
                $client->aliases()->create([
                    'full_name' => $display_name,
                    'nric_no' => $identity_no,
                    'associate_uuid' => $this->uuid
                ]);
            }


        }
        return $client->fresh();
    }


    public function findOrNewClientViaPolicy($provider_slug, $policy_no, $client_name, $client_nric = null) {
        $client = $this->findClient($client_name, $client_nric);

        // Function to return Client instance.
        // Check if policy table contain records of (provider & policy_no)
        if ($policy = $this->policies()->where('provider_slug', $provider_slug)->where('policy_no', $policy_no)->first()) {
            // Provider/Policy combination already exists, lets return the client that it belongs to...
            $policy_holder = $policy->policy_holder;
            $search = $policy_holder->aliases->filter(function($ca) use ($client_name) {
                return Common::trimStringUpper($ca->full_name) == Common::trimStringUpper($client_name);
            });

            if ($search->isEmpty()) {
                // Policy Holder exists, but searched name doesnt match any names inside list of Client aliases...
                // Create new Client's Aliases
                $policy_holder->aliases()->create([
                    'full_name' => Common::trimStringUpper($client_name),
                    'nric_no' => Common::trimStringUpper($client_nric) ?? null,
                    'associate_uuid' => $this->uuid
                ]);

                $individual = $policy_holder->individual;
                if ($client_nric) $individual->update([ Common::id_type($client_nric) => $client_nric ]);

            } else if ($client_nric) {
                // Search is not Empty && Client_nric value exists
                $search_nric = $search->filter(function($cnric) use ($client_nric) {
                    return Common::trimStringUpper($cnric->nric_no) == Common::trimStringUpper($client_nric);
                });

                if ($search_nric->isEmpty()) {
                    // Client with name match exists, Search for NRIC but nothing is returned
                    // Update client with $client_nric value
                    $alias = $search->first();
                    $alias->update(['nric_no' => $client_nric]);

                    $individual = $policy_holder->individual;
                    if ($client_nric) $individual->update([ Common::id_type($client_nric) => $client_nric ]);
                }
            }

            // Now that we have the Client model ready to be returned
            // Lets take a step further and see if current associate has other client data who are of name+nric combo
            if ($client && !$client->is($policy_holder)) {
                // Policyholder exists (Client $client)
                // Client exists (Client $test_client)
                // But the 2 models are not the same, so lets combine the 2 models together...
                $client->mergeTo($policy_holder);
            }

            return $policy_holder->fresh();
        } else {
            // Provider/Policy combination does not exists, lets create a client and return it.
            return $this->findOrNewClient($client_name, $client_nric);
        }
    }
}
