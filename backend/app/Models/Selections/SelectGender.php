<?php

namespace App\Models\Selections;
use App\Models\Selections\BaseModel;
use App\Traits\ScopeActivated;

class SelectGender extends BaseModel
{
    use ScopeActivated;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'genders';
}
