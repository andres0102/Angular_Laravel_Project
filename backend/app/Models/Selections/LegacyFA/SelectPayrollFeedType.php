<?php

namespace App\Models\Selections\LegacyFA;
use App\Models\Selections\BaseModel;
use App\Models\Selections\LegacyFA\{SelectPayrollFeedMapping, SelectPayrollCategory, SelectProvider};
use App\Traits\{ScopeContinuePayrollCategory, ScopeContinuePayrollEra, ScopeContinueProviderAlias, ScopeType};

class SelectPayrollFeedType extends BaseModel
{
    use ScopeContinuePayrollCategory, ScopeContinuePayrollEra, ScopeContinueProviderAlias, ScopeType;
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = '_lfa_payroll_comm_feed_types';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function provider() { return $this->belongsTo(SelectProvider::class, 'provider_slug', 'slug'); }
    public function category() { return $this->belongsTo(SelectPayrollCategory::class, 'payroll_cat_slug', 'slug'); }
    public function mapping() { return $this->belongsTo(SelectPayrollFeedMapping::class, 'payroll_mapping_id'); }
}
