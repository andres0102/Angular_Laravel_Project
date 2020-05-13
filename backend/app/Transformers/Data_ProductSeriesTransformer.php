<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class Data_ProductSeriesTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform($product)
    {
        $thumbnail_png = 'providers/logos/' . $product->provider_alias . '.png';
        $thumbnail_1x = 'providers/logos/' . $product->provider_alias . '.jpg';
        $thumbnail_2x = 'providers/logos/' . $product->provider_alias . '@2x.jpg';

        $product_thumbnail = 'providers/' . $product->provider_alias . '/thumbnails/' . $product->reference_thumbnail;
        $product_brochure = 'providers/' . $product->provider_alias . '/brochures/' . $product->reference_brochure;

        return [
            'uuid' => $product->uuid,
            'provider' => [
                'name' => $product->provider_name,
                'alias' => $product->provider_alias,
                'color' => $product->provider_color,
                'background' => $product->provider_background,
                'thumbnail_png' => file_exists(storage_path('app/public/'.$thumbnail_png)) ? asset('storage/'.$thumbnail_png) : null,
                'thumbnail_1x' => file_exists(storage_path('app/public/'.$thumbnail_1x)) ? asset('storage/'.$thumbnail_1x) : null,
                'thumbnail_2x' => file_exists(storage_path('app/public/'.$thumbnail_2x)) ? asset('storage/'.$thumbnail_2x) : null,
            ],
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'currency' => $product->currency,
            'tax' => (boolean) $product->tax,
            'reference_url' => $product->reference_url,
            'options_count' => $product->options_count ?? 0,
            'riders_count' => $product->riders_count ?? 0,
            'thumbnail' => file_exists(storage_path('app/public/' . $product_thumbnail)) ? asset('storage/'.$product_thumbnail) : null,
            'brochure' => file_exists(storage_path('app/public/' . $product_brochure)) ? asset('storage/'.$product_brochure) : null,
        ];
    }
}
