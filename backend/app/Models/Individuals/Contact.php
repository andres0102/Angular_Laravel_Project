<?php

namespace App\Models\Individuals;
use App\Models\Individuals\{BaseModel, Individual};
use App\Models\Selections\SelectContactType;

class Contact extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contacts';

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
    public function type() { return $this->belongsTo(SelectContactType::class, 'contact_type_slug', 'slug'); }
}
