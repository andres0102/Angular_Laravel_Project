<?php

namespace App\Models\Selections\LegacyFA;
use App\Models\Selections\BaseModel;
use App\Models\Selections\LegacyFA\SelectSubmissionCategory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ScopeFirstAlias;

class SelectProvider extends BaseModel
{
    use SoftDeletes;
    use ScopeFirstAlias;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = '_lfa_providers';

    /** ===================================================================================================
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /** ===================================================================================================
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at'
    ];

    public function submission_cat() { return $this->belongsToMany(
                                                        SelectSubmissionCategory::class,
                                                        '_lfa_providers_has_submission_cat',
                                                        'provider_slug',
                                                        'submission_cat_slug',
                                                        'slug','slug'); }
}
