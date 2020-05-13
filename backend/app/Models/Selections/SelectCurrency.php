<?php

namespace App\Models\Selections;
use App\Models\Selections\{BaseModel, SelectCountry};

class SelectCurrency extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'currencies';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function countries()
    {
        return $this->belongsToMany(SelectCountry::class, 'countries_has_currencies', 'currency_id', 'country_id');
    }
}
