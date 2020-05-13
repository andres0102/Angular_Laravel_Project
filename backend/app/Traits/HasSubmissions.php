<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\LegacyFA\Submissions\{Submission, SubmissionCase};

trait HasSubmissions
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function submissions() { return $this->hasMany(Submission::class, 'associate_uuid', 'uuid'); }
    public function submissions_cases() { return $this->hasMany(SubmissionCase::class, 'associate_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function submissionsApe($year) { return $this->submissions_cases()->whereHas('submission', function (Builder $query) use ($year) {
        $query->where('status_slug', 'submitted')->whereYear('date_submission', '=', $year);
    })->sum('ape'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    // public function hasPolicy($provider_id, $policy_no, $policy_array) {
    //     if (isset($policy_array['life_assured_name'])) {
    //         $life_assured_name = $policy_array['life_assured_name'];
    //         $life_assured_nric = $policy_array['life_assured_nric'] ?? null;
    //         $life_assured = $this->hasLifeAssured($life_assured_name, $life_assured_nric);
    //     }

    //     $search = $this->policies()->updateOrCreate([
    //         'provider_id' => $provider_id,
    //         'policy_no' => $policy_no,
    //         'associate_uuid' => $this->associate_uuid,
    //         'client_uuid' => $this->uuid
    //     ], array_filter($policy_array));

    //     if (isset($life_assured)) $search->update(['life_assured_uuid' => $life_assured->uuid]);

    //     return $search;
    // }
}
