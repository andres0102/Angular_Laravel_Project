<?php

namespace App\Models\Individuals;
use App\Models\Individuals\{BaseModel, Individual};
use App\Models\Selections\{SelectBank};

class Bank extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'banks';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'individual_uuid'
    ];

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function individual() { return $this->belongsTo(Individual::class, 'individual_uuid', 'uuid'); }
    public function bank() { return $this->belongsTo(SelectBank::class, 'bank_slug', 'slug'); }
}
