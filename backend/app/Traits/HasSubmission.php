<?php

namespace App\Traits;

use App\Models\LegacyFA\Submissions\{Submission, SubmissionCase};

trait HasSubmission
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function submissions() { return $this->hasMany(Submission::class, 'client_uuid', 'uuid'); }
    public function submission_cases() { return $this->hasMany(SubmissionCase::class, 'client_uuid', 'uuid'); }
}
