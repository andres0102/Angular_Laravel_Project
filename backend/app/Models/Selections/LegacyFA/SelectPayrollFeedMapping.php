<?php

namespace App\Models\Selections\LegacyFA;
use Illuminate\Database\Eloquent\Model;

class SelectPayrollFeedMapping extends Model
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
    protected $table = '_lfa_payroll_comm_feed_mapping';

    /** ===================================================================================================
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /** ===================================================================================================
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /** ===================================================================================================
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];

    /** ===================================================================================================
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /** ===================================================================================================
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /** ===================================================================================================
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}
