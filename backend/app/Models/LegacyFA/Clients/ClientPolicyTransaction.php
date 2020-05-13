<?php

namespace App\Models\LegacyFA\Clients;
use Illuminate\Support\Str;
use App\Models\LegacyFA\Clients\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\{AttributeName, AttributeCode, ScopeFirstUuid};

class ClientPolicyTransaction extends BaseModel
{
    use SoftDeletes, ScopeFirstUuid, AttributeName, AttributeCode;

    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_policies';

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'uuid'
    ];

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /** ===================================================================================================
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

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
}
