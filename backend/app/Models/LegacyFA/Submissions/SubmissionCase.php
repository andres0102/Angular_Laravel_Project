<?php

namespace App\Models\LegacyFA\Submissions;
use App\Models\LegacyFA\Submissions\{BaseModel, Submission, SubmissionCaseInfo, SubmissionCaseRiders, IntroducerCase};
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\{ScopeFirstUuid};
use Spatie\MediaLibrary\HasMedia\{HasMedia, HasMediaTrait};
use App\Models\Selections\LegacyFA\{SelectProvider, SelectSubmissionCategory, SelectProductCategory};
use App\Models\LegacyFA\Clients\{Client, LifeAssured};
use App\Models\LegacyFA\Associates\{Associate};
use App\Models\LegacyFA\Products\{Rider};
use App\Models\Media\Media;

class SubmissionCase extends BaseModel implements HasMedia
{
    use HasMediaTrait, ScopeFirstUuid;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cases';


    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'uuid'
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



    protected $casts = [
        'life_assured_personal' => 'array',
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
        self::created(function ($model) {
            $submission = $model->submission;
            $submission->log(auth()->user(), 'submission_case_created', 'Submission Case created.', null, $model, 'submissions_cases', $model->uuid);
        });
    }


    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function submission() { return $this->belongsTo(Submission::class, 'submission_uuid', 'uuid')->withTrashed(); }
    public function introducer_case() { return $this->hasMany(IntroducerCase::class, 'introducer_uuid', 'uuid'); }
    public function policy_holder() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function life_assured() { return $this->belongsTo(LifeAssured::class, 'life_assured_uuid', 'uuid'); }
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function provider() { return $this->belongsTo(SelectProvider::class, 'provider_slug', 'slug'); }
    public function submission_cat() { return $this->belongsTo(SelectSubmissionCategory::class, 'submission_cat_slug', 'slug'); }
    public function product_cat() { return $this->belongsTo(SelectProductCategory::class, 'product_cat_slug', 'slug'); }

    public function info() { return $this->hasMany(SubmissionCaseInfo::class, 'case_uuid', 'uuid'); }
    public function riders() { return $this->belongsToMany(Rider::class, 'lfa_submissions.case_riders', 'case_uuid', 'rider_uuid', 'uuid', 'uuid')->withPivot('rider_name')->withTimestamps(); }

    public function documents() { return $this->hasMany(Media::class, 'model_id', 'id')->where('model_type', 'submissions_cases'); }

    /** ===================================================================================================
     * Media Collections
     *
     */
    public function getMediaClientIdentityAttribute() { return $this->getMedia('documents'); }
    // public function addImage($pathToFile)
    // {
    //     return $this->addMedia($pathToFile)
    //                 ->setName($this->name)
    //                 ->setFileName($this->uuid . '.' . pathinfo($pathToFile, PATHINFO_EXTENSION))
    //                 ->toMediaCollection('product-images');
    // }
    public function registerMediaCollections()
    {
        $this->addMediaCollection('application-form')->useDisk('submissions');
        $this->addMediaCollection('benefit-illustration')->useDisk('submissions');
        $this->addMediaCollection('giro-form')->useDisk('submissions');
        $this->addMediaCollection('medical-report')->useDisk('submissions');
        $this->addMediaCollection('supplementary')->useDisk('submissions');
        $this->addMediaCollection('documents')->useDisk('submissions');
    }
}
