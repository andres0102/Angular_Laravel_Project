<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Transformers\{Data_ProductSeriesTransformer, Data_ProductOptionTransformer};

class ProductHelper
{
    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function index($product = null)
    {


        // Begin to merge DB tables
        $query = DB::connection('lfa_products')
                    ->table('products as product')
                    ->where('product.activated', true)
                    ->where('product.deleted_at', null)
                    ->leftJoin('lfa_selections._lfa_providers as provider', 'product.provider_slug', '=', 'provider.slug')
                    ->select(
                        'product.*',
                        'provider.full_name as provider_name',
                        'provider.alias as provider_alias',
                        'provider.color as provider_color',
                        'provider.background as provider_background',
                        // 'product_cat.category as product_cat',
                        // 'product_cat.title as product_cat_title',
                        // 'product_cat.permission as product_cat_permission',
                        DB::raw("(SELECT count(*) FROM lfa_products.product_options WHERE product_uuid = product.uuid) as options_count"),
                        DB::raw("(SELECT count(*) FROM lfa_products.product_options_has_riders WHERE product_option_uuid IN (SELECT uuid from lfa_products.product_options WHERE product_uuid = product.uuid)) as riders_count")
                    )->orderBy('product.name');

        if ($product) {
            $results = $query->where('product.uuid', $product->uuid);
            return fractal($results->first(), new Data_ProductSeriesTransformer())->toArray()['data'];
        } else {
            return fractal($query->get()->toArray(), new Data_ProductSeriesTransformer())->toArray()['data'];
        }
    }


    /** ===================================================================================================
    * Query and return Associate record with joined fields
    **/
    public static function options($series, $option = null)
    {
        // Product Categories
        $product_cat = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'title', title,
                            'slug', slug,
                            'category', category,
                            'permission', permission
                        )) FROM lfa_selections._lfa_product_categories product_cat WHERE product_cat.slug = product_cat_slug) as product_cat";

        // Riders
        $has_riders = "(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                            'uuid', uuid,
                            'name', name
                        )) FROM lfa_products.riders WHERE uuid IN (SELECT rider_uuid from lfa_products.product_options_has_riders orider where orider.product_option_uuid = option.uuid)) as riders";

        // Begin to merge DB tables
        $query = DB::connection('lfa_products')
                    ->table('product_options as option')
                    ->where('option.activated', true)
                    ->where('product_uuid', $series->uuid)
                    ->where('option.deleted_at', null)
                    ->select(
                        'option.*',
                        DB::raw($product_cat),
                        DB::raw($has_riders),
                        DB::raw("(SELECT count(*) FROM lfa_products.product_options_has_riders WHERE product_option_uuid = option.uuid) as riders_count")
                    )->orderBy('option.name');

        if ($option) {
            $results = $query->where('option.uuid', $option->uuid);
            return fractal($results->first(), new Data_ProductOptionTransformer())->toArray()['data'];
        } else {
            return fractal($query->get()->toArray(), new Data_ProductOptionTransformer())->toArray()['data'];
        }
    }
}