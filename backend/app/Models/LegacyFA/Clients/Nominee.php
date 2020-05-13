<?php

namespace App\Models\LegacyFA\Clients;

use Illuminate\Support\Str;
use App\Models\LegacyFA\Clients\{BaseModel, Introducer, Client};
use App\Models\LegacyFA\Submissions\IntroducerCase;
use App\Models\Selections\{SelectRelationshipType};
use App\Models\Selections\LegacyFA\{SelectNomineeBenefit,SelectNomineeStatus};
use App\Traits\{ScopeFirstUuid, HasIndividual};

class Nominee extends BaseModel
{
    use ScopeFirstUuid, HasIndividual;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nominees';

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
            $client = $model->client;
            $client->log(auth()->user(), 'nominee_created', 'Nominee record created.', null, $model, 'nominees', $model->uuid);
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function client() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function converted_client() { return $this->belongsTo(Client::class, 'converted_client_uuid', 'uuid'); }
    public function introducer() { return $this->belongsTo(Introducer::class, 'introducer_uuid', 'uuid'); }
    public function relationship_type() { return $this->belongsTo(SelectNomineeBenefit::class, 'relationship_type_slug', 'uuid'); }
    public function nominee_benefit() { return $this->belongsTo(SelectRelationshipType::class, 'nominee_benefit_slug', 'uuid'); }
    public function nominee_status() { return $this->belongsTo(SelectNomineeStatus::class, 'nominee_status_slug', 'uuid'); }
    public function introducer_case() { return $this->hasMany(IntroducerCase::class, 'introducer_uuid', 'uuid'); }
}
