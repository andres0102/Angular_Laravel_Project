<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Helpers\Common;
use App\Models\Media\Media;

class Data_ClientTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($client)
    {
        return array_merge([
            'uuid' => $client->uuid,
            'display_name' => strtoupper($client->display_name),
            'aliases' => strtoupper($client->aliases_names),
            'lead_stage_slug' => $client->lead_stage_slug,
            'sales_stage_slug' => $client->sales_stage_slug,
            'client_type_slug' => $client->client_type_slug,
            'source_slug' => $client->source_slug,
            'submissions_count' => (int) $client->submissions_count,
            'policies_count' => (int) $client->policies_count,
            'life_assured_count' => (int) $client->life_assured_count,
            'nominees_count' => (int) $client->nominees_count,
            'display_photo' => [
                'original' => null, // ($client->profile) ? Media::whereId($client->profile)->first()->getFullUrl() : null,
                'thumbnail' => null, // ($client->profile) ? Media::whereId($client->profile)->first()->getFullUrl('thumbnail') : null,
            ],
            'associate' => [
                'uuid' => $client->associate_uuid,
                'name' => $client->associate_name,
            ],
            'business' => ($client->client_type_slug == 'business') ? [
                'name' => strtoupper($client->business_name),
                'uen' => strtoupper($client->business_uen),
            ] : null,
            'introducer_scheme' => json_decode($client->introducer_scheme),
            'is_nominee' => ($client->nominee) ? true : false,
            'description' => $client->description,
            'interest' => $client->interest,
            'important' => $client->important,
            'created_at' => $client->created_at,
            'updated_at' => $client->updated_at,
            'deleted_at' => $client->deleted_at,
        ], Common::lfa_individual_data(json_decode($client->individual, true)));
    }
}
