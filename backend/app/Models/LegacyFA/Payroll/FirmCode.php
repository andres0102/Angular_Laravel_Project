<?php

namespace App\Models\LegacyFA\Payroll;
use App\Models\LegacyFA\Payroll\BaseModel;
use App\Traits\ScopeContinueProviderAlias;

class FirmCode extends BaseModel
{
    use ScopeContinueProviderAlias;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'firm_codes';
}
