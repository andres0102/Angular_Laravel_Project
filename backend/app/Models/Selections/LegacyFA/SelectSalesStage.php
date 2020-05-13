<?php

namespace App\Models\Selections\LegacyFA;
use App\Models\Selections\BaseModel;
use App\Traits\ScopeFirstStep;

class SelectSalesStage extends BaseModel
{
    use ScopeFirstStep;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = '_lfa_sales_stage';
}