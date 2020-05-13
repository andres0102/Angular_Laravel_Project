<?php

namespace App\Traits;

use App\Models\LegacyFA\Payroll\{PayrollBatch, PayrollFeed, PayrollRecord};
use App\Models\Selections\LegacyFA\{SelectPayrollCategory, SelectPayrollFeedType, SelectProvider};

trait TraitPayroll
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function batch() { return $this->belongsTo(PayrollBatch::class, 'batch_id'); }
    public function feed() { return $this->belongsTo(PayrollFeed::class, 'feed_id'); }
    public function record() { return $this->belongsTo(PayrollRecord::class, 'record_id'); }
    public function category() { return $this->belongsTo(SelectPayrollCategory::class, 'payroll_cat_slug', 'slug'); }
    public function provider() { return $this->belongsTo(SelectProvider::class, 'provider_slug', 'slug'); }
    public function feed_type() { return $this->belongsTo(SelectPayrollFeedType::class, 'payroll_type_slug', 'slug'); }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getCategoryNameAttribute() { return $this->category->title; }
    public function getProviderNameAttribute() { return ($this->provider) ? $this->provider->name : null; }
    public function getProviderAliasAttribute() { return($this->provider) ?  $this->provider->alias : null; }
    public function getFeedTypeNameAttribute() { return $this->feed_type->title; }

    public function getIsAaAttribute() { return ($this->feed_type->era === 'aa'); }
    public function getIsLfaAttribute() { return ($this->feed_type->era === 'lfa'); }
    public function getIsGiAttribute() { return ($this->feed_type->era === 'gi'); }
}
