<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class Data_ProductOptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($option)
    {
        return [
            'uuid' => $option->uuid,
            'name' => $option->name,
            'premium_type' => $option->premium_type,
            'participating' => $option->participating,
            'date_start' => $option->date_start,
            'date_end' => $option->date_end,
            'product_cat' => collect(json_decode($option->product_cat))->first(),
            'riders' => collect(json_decode($option->riders)),
            'riders_count' => (int) $option->riders_count
        ];
    }
}
