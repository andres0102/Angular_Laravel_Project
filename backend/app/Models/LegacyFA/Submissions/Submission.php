<?php

namespace App\Models\LegacyFA\Submissions;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia\{HasMedia, HasMediaTrait};
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Selections\LegacyFA\{SelectProvider, SelectClientSource, SelectSubmissionStatus};
use App\Models\LegacyFA\Clients\{Client, Introducer, Nominee};
use App\Models\LegacyFA\Associates\{Associate};
use App\Models\LegacyFA\Submissions\{BaseModel, SubmissionCase, IntroducerCase};
use App\Traits\{ScopeFirstPid, ScopeFirstUuid, HasLogs};
use App\Models\Media\Media;

class Submission extends BaseModel implements HasMedia
{
    use ScopeFirstPid, ScopeFirstUuid,
        SoftDeletes,
        HasMediaTrait,
        HasLogs;


    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'submissions';


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
        'client_personal' => 'array',
        'client_business' => 'array',
        'date_submission' => 'datetime:Y-m-d',
    ];
    public function setDateSubmissionAttribute($date) { $this->attributes['date_submission'] = ($date) ? Carbon::parse($date) : null; }


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
            // Log Submission
            $model->log(auth()->user(), 'submission_created', 'Submission record created.', null, $model, 'submissions', $model->uuid);
            // Log Client
            $client = $model->policy_holder;
            $client->update([
                'is_lead' => false,
                'lead_stage_slug' => 'converted-to-client',
                'sales_stage_slug' => 'submission',
            ]);
            $client->log(auth()->user(), 'submission_created', 'Submission record created.', null, $model, 'submissions', $model->uuid);
        });
    }


    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function policy_holder() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function introducer() { return $this->belongsTo(Introducer::class, 'introducer_uuid', 'uuid'); }
    public function nominee() { return $this->belongsTo(Nominee::class, 'nominee_uuid', 'uuid'); }
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function cases() { return $this->hasMany(SubmissionCase::class, 'submission_uuid', 'uuid'); }
    public function introducer_case() { return $this->hasMany(IntroducerCase::class, 'submission_uuid', 'uuid'); }
    public function status() { return $this->belongsTo(SelectSubmissionStatus::class, 'status_slug', 'slug'); }
    public function documents() { return $this->hasMany(Media::class, 'model_id', 'id')->where('model_type', 'submissions'); }


    /** ===================================================================================================
     * Media Collections
     *
     */

    public function getMediaClientIdentityAttribute() { return $this->getMedia('client-identity'); }
    // public function addImage($pathToFile)
    // {
    //     return $this->addMedia($pathToFile)
    //                 ->setName($this->name)
    //                 ->setFileName($this->uuid . '.' . pathinfo($pathToFile, PATHINFO_EXTENSION))
    //                 ->toMediaCollection('product-images');
    // }
    public function registerMediaCollections()
    {
        $this->addMediaCollection('client-identity')->useDisk('submissions');
        $this->addMediaCollection('proof-of-address')->useDisk('submissions');
        $this->addMediaCollection('pfr')->useDisk('submissions');
        $this->addMediaCollection('submission-checklist')->useDisk('submissions');
        $this->addMediaCollection('other-documents')->useDisk('submissions');
    }
}
