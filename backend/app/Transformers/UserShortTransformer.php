<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\LegacyFA\Associates\Associate;
use App\Models\Users\User;
use App\Helpers\Common;

class UserShortTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        $array = [
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => strtolower($user->email),
            'gender' => $user->gender->title,
            'designation' => $user->designation->title,
            'last_seen' => ($user->last_seen) ? $user->last_seen->toDateTimeString() : null,
            'dp_1x' => Common::dataOrNull($user->dp_thumb),
            'dp_2x' => Common::dataOrNull($user->dp_original),
            'activated' => (boolean) $user->activated
        ];

        return $array;
    }
}
