<?php

namespace App\Models\General;

use Illuminate\Support\Str;

use App\Models\General\{BaseModel};
use App\Models\Users\User;
use App\Traits\{HasLogs, HasComments, ScopeFirstUuid};

class Notice extends BaseModel
{
    use HasLogs, HasComments, ScopeFirstUuid;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notices';

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
     * Eloquent Model Relationships
     * @var array
     */
    public function author() { return $this->belongsTo(User::class, 'user_uuid', 'uuid'); }
}
