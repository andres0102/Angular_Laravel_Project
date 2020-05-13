<?php

namespace App\Models\Selections\LegacyFA;
use App\Models\Selections\BaseModel;
use App\Models\Selections\LegacyFA\SelectProductCategory;

class SelectProductCoverage extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = '_lfa_product_coverage';

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function product_cat() { return $this->belongsTo(SelectProductCategory::class, 'product_cat_slug', 'slug'); }
}
