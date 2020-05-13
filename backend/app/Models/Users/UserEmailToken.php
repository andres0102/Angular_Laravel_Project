<?php

namespace App\Models\Users;
use App\Models\Users\BaseModel;
use Carbon\Carbon;

class UserEmailToken extends BaseModel
{
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'token';

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'verify_email';

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
    public function user() { return $this->belongsTo('App\Models\Users\User', 'email', 'email'); }

    /** ===================================================================================================
     * Returns if the token is still valid based on timestamp.
     *
     * @return mixed
     */
    public function valid()
    {
        $now = Carbon::now();
        $validDifference = 60 * 60;
        // $validDifference = 60 * 60 * 8;
        return $this->created_at->diffInSeconds($now) <= $validDifference;
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}
