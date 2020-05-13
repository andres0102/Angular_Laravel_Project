<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class UserPermission extends Permission
{
    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_users';
}
