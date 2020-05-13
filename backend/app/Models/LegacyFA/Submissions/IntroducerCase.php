<?php

namespace App\Models\LegacyFA\Submissions;

use Illuminate\Support\Str;

use App\Models\Selections\SelectBank;
use App\Models\LegacyFA\Submissions\{BaseModel, Submission};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\{Client, Introducer, Nominee};

class IntroducerCase extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'introducer_cases';


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
            $submission->log(auth()->user(), 'introducer_case_created', 'Introducer Case created.', null, $model, 'introducer_cases', $model->uuid);
            $client = $model->client;
            $client->log(auth()->user(), 'introducer_case_created', 'Introducer Case created.', null, $model, 'introducer_cases', $model->uuid);
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function bank() { return $this->belongsTo(SelectBank::class, 'bank_slug', 'slug'); }
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function client() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function introducer() { return $this->belongsTo(Introducer::class, 'introducer_uuid', 'uuid'); }
    public function nominee() { return $this->belongsTo(Nominee::class, 'nominee_uuid', 'uuid'); }
    public function submission() { return $this->belongsTo(Submission::class, 'submission_uuid', 'uuid')->withTrashed(); }
}
