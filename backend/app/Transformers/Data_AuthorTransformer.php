<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Helpers\Common;
use App\Models\Users\User;

class Data_AuthorTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($author)
    {
        $user = User::firstUuid($author);
        return [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'gender_slug' => $user->individual->gender_slug,
            'designation' => $user->designation->title,
            'display_photo' => [
                'original' => $user->dp_original,
                'thumbnail' => $user->dp_thumb,
            ],
        ];
    }
}
