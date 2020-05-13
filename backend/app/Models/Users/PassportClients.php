<?php

namespace App\Models\Users;

use Webpatser\Uuid\Uuid;
use Laravel\Passport\Client;

class PassportClients extends Client
{
    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_users';

    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        parent::boot();
        // self::creating(function ($model) {
        //     $model->uuid = (string) Uuid::generate();
        // });
    }

    /**
     * Get the user that the client belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.'.config('auth.guards.api.provider').'.model')
        );
    }
}
