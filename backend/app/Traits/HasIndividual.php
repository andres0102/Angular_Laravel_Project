<?php

namespace App\Traits;

use App\Models\Individuals\Individual;

trait HasIndividual
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function individual() { return $this->belongsTo(Individual::class, 'individual_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getNameAttribute() { return ($this->individual) ? strtoupper($this->individual->full_name) : null; }
    public function getAliasAttribute() { return ($this->individual) ? strtoupper($this->individual->alias) : null; }

    public function getGenderAttribute() { return ($this->individual) ? $this->individual->gender->title : null; }
    public function getMaritalStatusAttribute() { return ($this->individual) ? $this->individual->marital_status->title : null; }
    public function getRaceAttribute() { return ($this->individual) ? $this->individual->race->title : null; }
    public function getCountryBirthAttribute() { return ($this->individual) ? $this->individual->country_birth->title : null; }
    public function getNationalityAttribute() { return ($this->individual) ? $this->individual->nationality->nationality : null; }
    public function getResidencyStatusAttribute() { return ($this->individual) ? $this->individual->residency_status->title : null; }
    public function getEmploymentStatusAttribute() { return ($this->individual) ? $this->individual->employment_status->title : null; }
    public function getEducationalLevelAttribute() { return ($this->individual) ? $this->individual->education_level->title : null; }

    public function getAddressesAttribute() { return ($this->individual) ? $this->individual->addresses : null; }
    public function getContactsAttribute() { return ($this->individual) ? $this->individual->contacts : null; }
    public function getBanksAttribute() { return ($this->individual) ? $this->individual->banks : null; }
}
