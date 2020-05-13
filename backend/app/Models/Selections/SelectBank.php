<?php

namespace App\Models\Selections;
use App\Models\Selections\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelectBank extends BaseModel
{
    use SoftDeletes;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'banks';

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
}
