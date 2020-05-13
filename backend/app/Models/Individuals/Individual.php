<?php

namespace App\Models\Individuals;
use Carbon\Carbon;
use App\Models\Individuals\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Selections\{SelectSalutation,
                           SelectGender,
                           SelectMaritalStatus,
                           SelectRace,
                           SelectCountry,
                           SelectResidencyStatus,
                           SelectEmploymentStatus,
                           SelectEducationalLevel};
use App\Models\Individuals\{Bank, Address, Contact};
use App\Traits\{ScopeFirstUuid};

class Individual extends BaseModel
{
    use SoftDeletes, ScopeFirstUuid;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'lfa_individuals.individuals';

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
        'date_birth' => 'datetime:Y-m-d',
        'deleted_at' => 'datetime',
    ];
    public function setDateBirthAttribute($date) { $this->attributes['date_birth'] = ($date) ? Carbon::parse($date) : null; }

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

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function salutation() { return $this->belongsTo(SelectSalutation::class, 'salutation_slug', 'slug'); }
    public function gender() { return $this->belongsTo(SelectGender::class, 'gender_slug', 'slug'); }
    public function marital_status() { return $this->belongsTo(SelectMaritalStatus::class, 'marital_status_slug', 'slug'); }
    public function race() { return $this->belongsTo(SelectRace::class, 'race_slug', 'slug'); }
    public function country_birth() { return $this->belongsTo(SelectCountry::class, 'country_birth_slug', 'slug'); }
    public function nationality() { return $this->belongsTo(SelectCountry::class, 'nationality_slug', 'slug'); }
    public function residency_status() { return $this->belongsTo(SelectResidencyStatus::class, 'residency_status_slug', 'slug'); }
    public function employment_status() { return $this->belongsTo(SelectEmploymentStatus::class, 'employment_status_slug', 'slug'); }
    public function education_level() { return $this->belongsTo(SelectEducationalLevel::class, 'education_level_slug', 'slug'); }

    public function addresses() { return $this->hasMany(Address::class, 'individual_uuid', 'uuid'); }
    public function contacts() { return $this->hasMany(Contact::class, 'individual_uuid', 'uuid'); }
    public function banks() { return $this->hasMany(Bank::class, 'individual_uuid', 'uuid'); }
    public function dependents() { return $this->belongsToMany(Individual::class, 'individuals_has_dependents', 'individual_uuid', 'dependent_uuid', 'uuid', 'uuid')->withPivot('relationship_type_slug'); }
}
