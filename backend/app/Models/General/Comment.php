<?php

namespace App\Models\General;
use App\Models\General\{BaseModel};
use App\Models\Users\User;
use Illuminate\Support\Str;

class Comment extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /** ===================================================================================================
     *  Setup model event hooks
     *
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = Str::orderedUuid()->toString();
        });
    }

    /** ===================================================================================================
     * Get the entity that the model belongs to.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function author() { return $this->belongsTo(User::class, 'user_uuid', 'uuid'); }
}
