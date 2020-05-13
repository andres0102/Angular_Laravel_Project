<?php

namespace App\Models\Selections;
use Illuminate\Database\Eloquent\Model;

class RefAAPolicyDates extends Model
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
    protected $table = '_aa_policy_dates';

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
    protected $dates = [
        'old_date',
        'new_date'
    ];

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

    /** ===================================================================================================
     * Scope a query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsAgent($query, $code)
    {
        return $query->where('agent_code', $code);
    }

    public function scopeIsPolicy($query, $policy_number)
    {
        return $query->where('policy_number', $policy_number);
    }

    public function scopeODate($query, $date)
    {
        return $query->where('old_date', $date);
    }
}
