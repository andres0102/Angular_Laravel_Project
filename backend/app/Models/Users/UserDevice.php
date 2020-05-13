<?php

namespace App\Models\Users;
use App\Models\Users\{BaseModel, User};

class UserDevice extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'devices';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'user_uuid'
    ];

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function user() { return $this->belongsTo(User::class, 'user_uuid', 'uuid'); }
}
