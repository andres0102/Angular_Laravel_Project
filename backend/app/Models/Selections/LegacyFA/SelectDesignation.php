<?php

namespace App\Models\Selections\LegacyFA;
use App\Models\Selections\BaseModel;

class SelectDesignation extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = '_lfa_designations';

    /** ===================================================================================================
     * Custom Functions
     */
    public function tier($num) {
        return ($this->salesforce_tier == $num);
    }
}
