<?php

namespace App\Models\Users;

use Laravel\Passport\Token;

class PassportTokens extends Token
{
    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_users';
}
