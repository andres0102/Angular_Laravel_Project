<?php

namespace App\Models\LegacyFA\Clients;
use App\Models\DefaultModel;

class BaseModel extends DefaultModel
{
    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_clients';
}
