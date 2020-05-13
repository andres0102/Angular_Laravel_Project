<?php

namespace App\Models\Selections\LegacyFA;
use App\Models\DefaultModel;

class SelectSubmissionProvider extends DefaultModel
{

    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_selections';

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = '_lfa_providers_has_submission_cat';

    /** ===================================================================================================
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
