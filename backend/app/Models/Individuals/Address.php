<?php

namespace App\Models\Individuals;
use App\Models\Individuals\{BaseModel, Individual};
use App\Models\Selections\{SelectAddressType, SelectCountry};

class Address extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'addresses';

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
    public function type() { return $this->belongsTo(SelectAddressType::class, 'address_type_slug', 'slug'); }
    public function country() { return $this->belongsTo(SelectCountry::class, 'country_slug', 'slug'); }
}
