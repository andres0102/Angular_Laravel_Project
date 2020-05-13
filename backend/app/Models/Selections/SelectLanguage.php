<?php

namespace App\Models\Selections;
use App\Models\Selections\{BaseModel, SelectCountry};

class SelectLanguage extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'languages';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function countries()
    {
        return $this->belongsToMany(SelectCountry::class, 'countries_has_languages', 'language_id', 'country_id');
    }
}
