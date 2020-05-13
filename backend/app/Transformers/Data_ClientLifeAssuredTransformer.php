<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

use App\Helpers\Common;

class Data_ClientLifeAssuredTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($la)
    {
        return array_merge([
            'uuid' => $la->uuid,
            'relationship_type_slug' => $la->relationship_type_slug,
            'name' => strtoupper(json_decode($la->individual, true)['full_name']),
        ], Common::lfa_individual_data(json_decode($la->individual, true)));
    }
}
