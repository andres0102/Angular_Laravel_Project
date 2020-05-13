<?php

namespace App\Models\Users;

use Laravel\Passport\PersonalAccessClient;

class PassportPersonnelAccessClient extends PersonalAccessClient
{
    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_users';
}
