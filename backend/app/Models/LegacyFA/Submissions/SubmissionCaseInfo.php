<?php

namespace App\Models\LegacyFA\Submissions;
use App\Models\LegacyFA\Submissions\{BaseModel, SubmissionCase};

class SubmissionCaseInfo extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'case_info';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function case() { return $this->belongsTo(SubmissionCase::class, 'case_uuid', 'uuid'); }
}
