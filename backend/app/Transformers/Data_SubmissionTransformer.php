<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Transformers\Data_SubmissionCaseTransformer;

use App\Helpers\Common;
use App\Models\Media\Media;

class Data_SubmissionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($submission)
    {
        return [
            'uuid' => $submission->uuid,
            'status' => $submission->status,
            'status_slug' => $submission->status_slug,
            'status_desc' => $submission->status_desc,
            'date_submission' => $submission->date_submission,
            'case_count' => $submission->case_count,
            'providers' => $submission->provider_names,
            'total_premiums' => $submission->premiums,
            'total_ape' => $submission->ape,
            'associate' => [
                'uuid' => $submission->associate_uuid,
                'name' => $submission->associate_name
            ],
            'media_count' => $submission->media_count,
            // 'media' => collect(json_decode($submission->media))->groupBy('collection_name'),
            'client' => [
                'uuid' => $submission->client_uuid,
                'display_name' => $submission->client_name,
                'client_type' => $submission->client_type,
                'description' => $submission->client_description,
                // 'display_photo' => [
                //     'original' => ($submission->client_profile) ? Media::whereId($submission->client_profile)->first()->getFullUrl() : null,
                //     'thumbnail' => ($submission->client_profile) ? Media::whereId($submission->client_profile)->first()->getFullUrl('thumbnail') : null,
                // ],
                'business' => ($submission->client_type == 'Business') ? [
                    'name' => strtoupper($submission->client_business_name),
                    'uen' => strtoupper($submission->client_business_uen),
                ] : null,
                'personal' => Common::lfa_individual_data(json_decode($submission->client_personal, true))['personal']
                // 'personal' => Common::lfa_individual_data(json_decode($submission->individual)[0])['personal'] ?? null,
            ],
            'remarks' => $submission->remarks,
            'created_at' => $submission->created_at,
            'updated_at' => $submission->updated_at,
            'deleted_at' => $submission->deleted_at,
        ];
    }
}
