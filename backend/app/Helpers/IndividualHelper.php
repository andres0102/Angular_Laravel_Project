<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;

use App\Models\Individuals\Individual;

class IndividualHelper
{
    /** ===================================================================================================
     * Function to return validations for table fields
     *
     */
    public static function validations($type = null, $required = false)
    {
      $required_str = ($required) ? 'required|' : '';

      switch ($type) {
        case 'contacts':
            return [
              'contact_type_slug' => 'nullable|string|exists:lfa_selections.contact_types,slug',
              'home_no' => 'nullable|string',
              'mobile_no' => 'nullable|string',
              'fax_no' => 'nullable|string',
              'email' => 'nullable|email',
            ];
            break;
        case 'addresses':
            return [
              'address_type_slug' => 'nullable|exists:lfa_selections.address_types,slug',
              'block' => 'nullable|string',
              'street' => 'nullable|string',
              'unit' => 'nullable|string',
              'building' => 'nullable|string',
              'city' => 'nullable|string',
              'postal' => 'nullable|string',
              'country_slug' => 'nullable|string|exists:lfa_selections.countries,slug',
            ];
            break;
        case 'banks':
            return [
              'bank_slug' => 'nullable|string|exists:lfa_selections.banks,slug',
              'account_no' => 'required|string',
            ];
            break;
        default:
            return [
              'full_name' => $required_str . 'string|max:255',
              'alias' => 'nullable|string',
              'chinese_name' => 'nullable',
              'nric_no' => 'nullable|string',
              'fin_no' => 'nullable|string',
              'passport_no' => 'nullable|string',
              'date_birth' => 'nullable|date',
              'salutation_slug' => 'nullable|string|exists:lfa_selections.salutations,slug',
              'gender_slug' => $required_str . 'string|exists:lfa_selections.genders,slug',
              'marital_status_slug' => 'nullable|string|exists:lfa_selections.marital_status,slug',
              'race_slug' => 'nullable|string|exists:lfa_selections.race,slug',
              'country_birth_slug' => 'nullable|string|exists:lfa_selections.countries,slug',
              'nationality_slug' => 'nullable|string|exists:lfa_selections.countries,slug',
              'residency_status_slug' => 'nullable|string|exists:lfa_selections.residency_status,slug',
              'employment_status_slug' => 'nullable|string|exists:lfa_selections.employment_status,slug',
              'job_title' => 'nullable|string',
              'company_name' => 'nullable|string',
              'business_nature' => 'nullable|string',
              'income_range' => 'nullable',
              'education_level_slug' => 'nullable|exists:lfa_selections.educational_levels,slug',
              'education_institution' => 'nullable|string',
              'field_of_study' => 'nullable|string',
              'smoker' => 'boolean',
              'selected' => 'boolean',
              'pdpa' => 'boolean',
            ];
      }
    }


    /** ===================================================================================================
     * Function to return valid table fields
     *
     */
    public static function fields($type = null)
    {
      return collect(self::validations($type))->keys()->all();
    }


    /** ===================================================================================================
     * Function to create Individual
     *
     */
    public static function create($data)
    {
      $data['full_name'] = Common::trimStringUpper($data['full_name']);

      if (Common::validData($data, 'nric_no')) {
        $data[Common::id_type($data['nric_no'])] = Common::trimStringUpper($data['nric_no']);

        return self::updateOrCreate($data);
      } else {
        $individual = Individual::create($data);
        return $individual;
      }
    }

    /** ===================================================================================================
     * Function to updateOrCreate Individual
     *
     */
    public static function updateOrCreate($data)
    {
      if (Common::validData($data, 'nric_no')) {
        $individual = Individual::updateOrCreate([
          'full_name' => Common::trimStringUpper($data['full_name']),
          Common::id_type($data['nric_no']) => Common::trimStringUpper($data['nric_no']),
        ], $data);
      } else {
        $individual = Individual::updateOrCreate([
          'full_name' => Common::trimStringUpper($data['full_name']),
        ], $data);
      }
      return $individual;
    }


    /** ===================================================================================================
     * Function to check and return existing Individual
     *
     */
    public static function check($full_name, $nric_no = null)
    {
      if ($nric_no) {
        return Individual::where('full_name', Common::trimStringUpper($full_name))
                          ->where(Common::id_type($nric_no), Common::trimStringUpper($nric_no))
                          ->first();
      } else {
        return Individual::where('full_name', Common::trimStringUpper($full_name))
                          ->first();
      }
    }


    /** ===================================================================================================
     * Function to update contact information
     *
     */
    public static function updateContact(Individual $individual, $data, $type = 'default')
    {
        $individual->contacts()->updateOrCreate([
          'contact_type_slug' => $type,
        ], $data);
    }


    /** ===================================================================================================
     * Function to update address information
     *
     */
    public static function updateAddress(Individual $individual, $data, $type = 'residential')
    {
        $individual->addresses()->updateOrCreate([
          'address_type_slug' => $type,
        ], $data);
    }


    /** ===================================================================================================
     * Function to update bank information
     *
     */
    public static function updateBank(Individual $individual, $data)
    {
        $individual->banks()->updateOrCreate($data);
    }


    /** ===================================================================================================
     * Function to create dependent
     *
     */
    public static function manageDependents(Individual $individual, $data)
    {
        $individual_check = self::check($data['full_name'], $data['nric_no'] ?? null);

        if ($individual->dependents()->contains($individual_check)) {
          // Dependents exists
          $individual_check->update($data);
        } else {
          // Individual exists, but not saved as dependent
          $individual_check = self::create($data);
        }
    }


}