<?php

namespace App\Traits;

use App\Models\Selections\LegacyFA\{SelectProductCategory, SelectProvider};

trait TraitProducts
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function category() { return $this->belongsTo(SelectProductCategory::class, 'payroll_cat', 'slug'); }
    public function provider() { return $this->belongsTo(SelectProvider::class, 'provider_slug', 'slug'); }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getCategoryNameAttribute() { return $this->category->title; }
    public function getProviderNameAttribute() { return $this->provider->name; }
    public function getProviderAliasAttribute() { return $this->provider->alias; }
}
