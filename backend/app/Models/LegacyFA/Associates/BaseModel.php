<?php

namespace App\Models\LegacyFA\Associates;
use App\Models\DefaultModel;

class BaseModel extends DefaultModel
{
    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_associates';
}
