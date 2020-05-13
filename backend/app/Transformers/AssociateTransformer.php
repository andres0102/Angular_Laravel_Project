<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\LegacyFA\Associates\Associate;

class AssociateTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Associate $associate)
    {
        return [
            'name' => $associate->name,
            'alias' => $associate->alias,
            'designation' => ($associate->latest_designation) ? $associate->latest_designation->title : null,
            'lfa_code' => strtoupper($associate->lfa_code),
            'email' => strtolower($associate->lfa_email),
            'uuid' => $associate->uuid,
            'gender' => $associate->gender,
            'dp_1x' => ($associate->dp_thumb) ? url($associate->dp_thumb) : null,
            'dp_2x' => ($associate->dp_original) ? url($associate->dp_original) : null,
            'is_manager' => (boolean) $associate->is_manager,
            'first_day' => $associate->first_day,
            'last_day' => $associate->last_day,
            'activated' => (boolean) $associate->activated
        ];
    }
}
