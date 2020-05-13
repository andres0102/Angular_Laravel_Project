<?php

namespace App\Models\LegacyFA\Clients;
use App\Models\LegacyFA\Clients\{BaseModel, Client};
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\{ScopeFirstUuid, HasIndividual};
use App\Models\LegacyFA\Associates\{Associate};
use App\Models\Selections\{SelectRelationshipType};
use Illuminate\Support\Str;

class LifeAssured extends BaseModel
{
    use SoftDeletes, HasIndividual, ScopeFirstUuid;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'life_assured';

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
            $client->log(auth()->user(), 'life_assured_created', 'Life Assured record created.', null, $model, 'life_assured', $model->uuid);
        });
    }


    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function policy_holder() { return $this->belongsTo(Client::class, 'policy_holder_uuid', 'uuid'); }
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function relationship_type() { return $this->belongsTo(SelectRelationshipType::class, 'relationship_type_slug', 'uuid'); }
}
