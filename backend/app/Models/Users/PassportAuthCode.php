<?php

namespace App\Models\Users;

use Laravel\Passport\AuthCode;

class PassportAuthCode extends AuthCode
{
    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_users';
}
