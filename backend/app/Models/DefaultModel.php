<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DefaultModel extends Model
{
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
    public $timestamps = true;

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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

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
