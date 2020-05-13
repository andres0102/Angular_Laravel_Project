<?php

namespace App\Http\Controllers\Associates;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

use App\Helpers\ProductHelper;
use App\Models\LegacyFA\Products\Product;

class AssociateProductController extends Controller
{
    /**
     * Create a new AssociateController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Middleware to check if user is logged in.
        // $this->middleware('auth');
    }


    /** ===================================================================================================
     * Get Product Catalog.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Check if authenticated user has permission to execute this action
        return response()->json([
            'error' => false,
            'data' => ProductHelper::index(null)
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LegacyFA\Associates\Associate  $associate
     * @return \Illuminate\Http\Response
     */
    public function show($slug, $option = null)
    {
        if ($series = Product::where('slug', $slug)->first()) {
            $provider = $series->provider;
            $thumbnail_png = 'providers/logos/' . $series->provider_alias . '.png';
            $thumbnail_1x = 'providers/logos/' . $series->provider_alias . '.jpg';
            $thumbnail_2x = 'providers/logos/' . $series->provider_alias . '@2x.jpg';

            $product_thumbnail = 'providers/' . $series->provider_alias . '/thumbnails/' . $series->reference_thumbnail;
            $product_brochure = 'providers/' . $series->provider_alias . '/brochures/' . $series->reference_brochure;

            return response()->json([
                'error' => false,
                'series' => [
                    'name' => $series->name,
                    'options' => $series->options()->count(),
                    'thumbnail' => file_exists(storage_path('app/public/' . $product_thumbnail)) ? asset('storage/'.$product_thumbnail) : null,
                    'provider' => [
                        'name' => $provider->full_name,
                        'color' => $provider->color,
                        'background' => $provider->background,
                        'thumbnail_png' => file_exists(storage_path('app/public/'.$thumbnail_png)) ? asset('storage/'.$thumbnail_png) : null,
                        'thumbnail_1x' => file_exists(storage_path('app/public/'.$thumbnail_1x)) ? asset('storage/'.$thumbnail_1x) : null,
                        'thumbnail_2x' => file_exists(storage_path('app/public/'.$thumbnail_2x)) ? asset('storage/'.$thumbnail_2x) : null,
                    ],
                ],
                'data' => ProductHelper::options($series)
            ]);
        } else {
            return Common::reject(404, 'series_not_found');
        }
    }
}
