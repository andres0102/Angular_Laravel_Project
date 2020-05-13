<?php

namespace App\Models\Users;

// Helpers
use Carbon\Carbon;
use App\Models\Users\BaseModel;

class UserResetPassword extends BaseModel
{
    public $incrementing = false;
    public $timestamps = false;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function user() { return $this->belongsTo('App\Models\Users\User', 'user_uuid', 'uuid'); }

    /** ===================================================================================================
     *  Setup model event hooks
     *
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->created_at = Carbon::now();
        });
    }

    /** ===================================================================================================
     * Returns if the token is still valid based on timestamp.
     *
     * @return mixed
     */
    public function valid()
    {
        $now = Carbon::now();
        $validDifference = 60 * 30;
        return $this->created_at->diffInSeconds($now) <= $validDifference;
    }
}
