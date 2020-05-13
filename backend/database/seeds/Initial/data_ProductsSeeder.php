<?php

use Carbon\Carbon;
use App\Helpers\Common;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Selections\LegacyFA\{SelectProvider, SelectProductCategory, SelectProductCoverage};
use App\Models\LegacyFA\Products\{Product, ProductOption, Rider};
use Illuminate\Support\Str;

class data_ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /** ===================================================================================================
       * Default LFA Product Listings
       */
      $this->command->info('Seeding :: LFA Product Listings');
      foreach (json_decode(Storage::get('seeders/product-listings.json'), true) as $data) {
        $provider = SelectProvider::firstAlias($data['provider']);
        $product = Product::updateOrCreate([
          'provider_slug' => $provider->slug,
          'reference_uen' => Str::slug($data['product_uen']),
        ], [
          'name' => $data['name'],
          'description' => (Common::validData($data, 'description')) ? $data['description'] : null,
          'reference_url' => (Common::validData($data, 'url')) ? $data['url'] : null,
          'reference_thumbnail' => (Common::validData($data, 'thumbnail')) ? $data['thumbnail'] : null,
          'reference_brochure' => (Common::validData($data, 'brochure')) ? $data['brochure'] : null,
          'tax' => (Common::validData($data, 'tax')) ? $data['tax'] : true,
        ]);
      }


      /** ===================================================================================================
       * Default LFA Product Options
       */
      $this->command->info('Seeding :: LFA Product Options');
      foreach (json_decode(Storage::get('seeders/product-options.json'), true) as $data) {
        $product = Product::firstUen(Str::slug($data['product_uen']));
        $provider = SelectProvider::firstAlias($data['provider']);
        $category = (Common::validData($data, 'category') && SelectProductCategory::firstSlug(Str::slug($data['category']))) ? Str::slug($data['category']) : null;

        $premium_type = null;
        if ($data['rp'] && $data['sp']) $premium_type = 'both';
        else if ($data['rp'] && !$data['sp']) $premium_type = 'regular';
        else if (!$data['rp'] && $data['sp']) $premium_type = 'single';

        $date_start = Common::parseDate($data, 'date_start', 'd/m/Y');
        $date_end = Common::parseDate($data, 'date_end', 'd/m/Y');

        $option = ProductOption::updateOrCreate([
          'provider_slug' => $provider->slug,
          'product_uuid' => $product->uuid,
          'reference_uen' => Str::slug($data['option_uen']),
        ], [
          'name' => $data['name'],
          'product_cat_slug' => $category,
          'reference_pid' => (Common::validData($data, 'pid')) ? $data['pid'] : null,
          'participating' => (Common::validData($data, 'par')) ? $data['par'] : false,
          'premium_type' => $premium_type,
          'date_start' => $date_start,
          'date_end' => $date_end,
        ]);
      }


      /** ===================================================================================================
       * Default LFA Product Riders
       */
      $this->command->info('Seeding :: LFA Riders');
      foreach (json_decode(Storage::get('seeders/product-riders.json'), true) as $data) {
        $provider = SelectProvider::firstAlias($data['provider']);
        $rider = Rider::updateOrCreate([
          'provider_slug' => $provider->slug,
          'reference_uen' => Str::slug($data['rider_uen']),
        ], [
          'name' => $data['name'],
          'reference_pid' => (isset($data['pid']) && !blank($data['pid'])) ? $data['pid'] : null,
        ]);
      }


      /** ===================================================================================================
       * Default LFA Product Options - Riders
       */
      $this->command->info('Seeding :: LFA Products has Riders');
      foreach (json_decode(Storage::get('seeders/product-has-riders.json'), true) as $data) {
        $product_option = ProductOption::firstUen(Str::slug($data['product_uen']));
        $rider = Rider::firstUen(Str::slug($data['rider_uen']));
        try {
          $product_option->riders()->attach($rider);
        } catch (Exception $e) {
          $this->command->info('Caught Exception: ' . $e->getMessage());
        }
      }
    }
}