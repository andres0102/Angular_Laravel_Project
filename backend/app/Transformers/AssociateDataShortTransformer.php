<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Helpers\Common;
use App\Models\Media\Media;

class AssociateDataShortTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($associate)
    {
        return [
            'associate' => [
                'uuid' => $associate->associate_uuid,
                'name' => $associate->associate_name,
                'gender_slug' => $associate->associate_gender,
                'designation' => $associate->associate_designation,
                'display_photo' => [
                    'original' => null, //($associate->associate_profile) ? Media::whereId($associate->associate_profile)->first()->getFullUrl() : null,
                    'thumbnail' => null, //($associate->associate_profile) ? Media::whereId($associate->associate_profile)->first()->getFullUrl('thumbnail') : null,
                ],
            ],
            'fyc' => $associate->fyc ?? 0,
            'ape' => $associate->ape ?? 0,
            'count' => $associate->count ?? 0
        ];
    }
}
