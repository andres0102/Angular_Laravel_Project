<?php

namespace App\Models\LegacyFA\Clients;
use Illuminate\Support\Str;
use App\Models\LegacyFA\Clients\{BaseModel, Client, LifeAssured, ClientPolicyTransaction};
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\{ScopeFirstUuid};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\Selections\LegacyFA\SelectProvider;

class ClientPolicy extends BaseModel
{
    use SoftDeletes, ScopeFirstUuid;

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
    protected $table = 'lfa_policies.policies';

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
        self::created(function ($model) {
            $client = $model->policy_holder;
            $client->log(auth()->user(), 'policy_created', 'Policy record created.', null, $model, 'client_policies', $model->uuid);
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function policy_holder() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function sales_associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function provider() { return $this->belongsTo(SelectProvider::class, 'provider_slug', 'slug'); }
    public function life_assured() { return $this->belongsTo(LifeAssured::class, 'life_assured_uuid', 'uuid'); }
    public function transactions() { return $this->hasMany(ClientPolicyTransaction::class, 'policy_uuid', 'uuid'); }
}
