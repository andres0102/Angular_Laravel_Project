<?php

namespace App\Models\Selections;
use App\Models\Selections\{BaseModel, SelectCurrency, SelectLanguage};

class SelectCountry extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function currencies() { return $this->belongsToMany(SelectCurrency::class, 'countries_has_currencies', 'country_id', 'currency_id'); }
    public function languages() { return $this->belongsToMany(SelectLanguage::class, 'countries_has_languages', 'country_id', 'language_id'); }
}
