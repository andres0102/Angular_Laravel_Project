<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

// Default Global Selections
use App\Models\Selections\{SelectCountry};

// Default Individual(s) Selections
use App\Models\Selections\{SelectSalutation,
                           SelectGender,
                           SelectRace,
                           SelectMaritalStatus,
                           SelectResidencyStatus,
                           SelectEducationalLevel,
                           SelectAddressType,
                           SelectContactType};

// Default LFA Selections
use App\Models\Selections\LegacyFA\{SelectDesignation,
                                    SelectProvider,
                                    SelectOnboardingStatus,
                                    SelectRNFStatus};

// Default Individual(s)
use App\Models\Users\{User};

// Default Individual(s)
use App\Models\Individuals\{Individual, Address, Contact};

// Default LFA Associate(s)
use App\Models\LegacyFA\Associates\{Associate, ProviderCode, Movement, BandingLFA, BandingGI};

// Default LFA Team(s)
use App\Models\LegacyFA\Teams\{Team, Membership};

use App\Helpers\{Common, IndividualHelper};


class data_CreateUpdateLfaAssociates extends Seeder
{
    /**
     * Set default variables.
     *
     */
    private $spouse_in_lfa = [];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      Membership::truncate();
      Movement::truncate();
      BandingLFA::truncate();
      BandingGI::truncate();

      /** ===================================================================================================
       * LFA :: Associates Records
       */
      $this->command->info('Seeding LFA Databases :: Associates Records');
      $this->associates = json_decode(Storage::get('seeders/associates.json'), true);
      foreach ($this->associates as $p_data) {
        if (Common::validData($p_data, 'company_email')) {
          $company_email = $p_data['company_email'];
        } else if (!Common::validData($p_data, 'company_email') && Common::validData($p_data, 'sl_no')) {
          $company_email = 'associate' . $p_data['sl_no'] . '@legacyfa-asia.com';
        } else {
          $company_email = null;
        }

        // Ignore records without valid company email and/or lfa_sl_no
        if ($company_email) {
            // Create or Update existing User record
            $onboarding_status = (Common::validData($p_data, 'lfa_status')) ? SelectOnboardingStatus::whereTitle($p_data['lfa_status'])->first()->slug : null;
            if (!$user = User::where('email', $company_email)->first()) {
              $user = User::create([
                'email' => $company_email,
                'password' => bcrypt($p_data['sl_no'] ?? '12345'),
              ]);
            }
            $user->update([
              'onboarding_status_slug' => $onboarding_status ?? null,
              'printer_id' => $p_data['printer_id'] ?? null,
              'did_no' => $p_data['did_no'] ?? null,
              'date_lfa_application' => Common::parseDate($p_data, 'date_lfa_application', 'd/m/Y'),
              'date_ceo_interview' => Common::parseDate($p_data, 'date_ceo_interview', 'd/m/Y'),
              'date_contract_start' => Common::parseDate($p_data, 'date_contract_start', 'd/m/Y'),
              'date_onboarded' => Common::parseDate($p_data, 'date_onboarded', 'd/m/Y'),
              'date_offboarded' => Common::parseDate($p_data, 'date_offboarded', 'd/m/Y'),
              'date_resigned' => Common::parseDate($p_data, 'date_resigned', 'd/m/Y'),
              'date_last_day' => Common::parseDate($p_data, 'date_lfa_last_day', 'd/m/Y'),
            ]);

            if (Common::parseDate($p_data, 'date_lfa_last_day', 'd/m/Y')) {
              $user->activated = false;
              $user->save();
            }

            // Create or Update existing Individual record
            $gender = (Common::validData($p_data, 'gender')) ? SelectGender::firstOrCreate(['title' => $p_data['gender']])->slug : null;
            $salutation = (Common::validData($p_data, 'salutation')) ? SelectSalutation::whereTitle($p_data['salutation'])->first()->slug : null;
            $race = (Common::validData($p_data, 'race')) ? SelectRace::firstOrCreate(['title' => $p_data['race']])->slug : null;
            $marital_status = (Common::validData($p_data, 'marital_status')) ? SelectMaritalStatus::firstOrCreate(['title' => $p_data['marital_status']])->slug : null;
            $education_level = (Common::validData($p_data, 'highest_education')) ? SelectEducationalLevel::whereTitle($p_data['highest_education'])->first()->slug : null;
            $nationality = (Common::validData($p_data, 'nationality')) ? SelectCountry::whereNationality($p_data['nationality'])->first()->slug : null;
            if (Common::validData($p_data, 'nationality') && $p_data['nationality'] == "Singaporean"){
              $residency_status = SelectResidencyStatus::whereTitle('Singaporean')->first()->slug;
            } else if (Common::validData($p_data, 'residency_status')) {
              $residency_status = SelectResidencyStatus::whereTitle($p_data['residency_status'])->first()->slug;
            }

            // User :: Individual record
            $individual_record = [
              'full_name' => Common::trimStringUpper($p_data['full_name']),
              'alias' => Common::trimStringUpper($p_data['alias']) ?? null,
              'nric_no' => Common::dataOrNull($p_data, 'nric_no'),
              'date_birth' => Common::parseDate($p_data, 'date_birth', 'd/m/Y'),
              'salutation_slug' => $salutation ?? null,
              'gender_slug' => $gender ?? null,
              'marital_status_slug' => $marital_status ?? null,
              'race_slug' => $race ?? null,
              'nationality_slug' => $nationality ?? null,
              'residency_status_slug' => $residency_status ?? null,
              'education_level_slug' => $education_level ?? null,
              'education_institution' => Common::dataOrNull($p_data, 'education_institution'),
              'field_of_study' => Common::dataOrNull($p_data, 'field_of_study'),
            ];

            if ($individual = $user->individual) {
              // User & individual created previously...
              // Update existing Individual record
              $individual->update($individual_record);
            } else {
              // Create new Individual record
              // because there shouldnt be any existing record if user is not even created
              // only allow update if full_name && nric_no matches exactly
              $individual = IndividualHelper::create($individual_record);
              $user->individual()->associate($individual)->save();
            }

            if (Common::validData($p_data, 'personal_email') || Common::validData($p_data, 'home') || Common::validData($p_data, 'mobile')) {
              IndividualHelper::updateContact($individual, [
                'home_no' => Common::dataOrNull($p_data, 'home'),
                'mobile_no' => Common::dataOrNull($p_data, 'mobile'),
                'email' => Common::dataOrNull($p_data, 'personal_email'),
              ]);
            }

            if (Common::validData($p_data, 'address') || Common::validData($p_data, 'address_postal')) {
              IndividualHelper::updateAddress($individual, [
                'street' => Common::dataOrNull($p_data, 'address'),
                'postal' => Common::dataOrNull($p_data, 'address_postal'),
              ]);
            }

            // LFA Associate :: Spouse record
            if (Common::validData($p_data, 'spouse_name')){
              // if spouse_sl_no, spouse is an agent, do not require to create individual record, just tag it will do
              // lets add to an array, just in case spouse is not created yet, end of operations den tag them
              if (Common::validData($p_data, 'spouse_sl_no')) {
                array_push($this->spouse_in_lfa,
                  [
                    'user_uuid' => $user->uuid,
                    'spouse_sl_no' => $p_data['spouse_sl_no']
                  ]);
              } else {
                $spouse_record = [
                    'full_name' => Common::trimStringUpper($p_data['spouse_name']),
                    'nric_no' => Common::dataOrNull($p_data, 'spouse_nric'),
                    'company_name' => Common::dataOrNull($p_data, 'spouse_company_name'),
                    'job_title' => Common::dataOrNull($p_data, 'spouse_job_title'),
                ];

                $spouse = IndividualHelper::updateOrCreate($spouse_record);
                $user->spouse()->associate($spouse)->save();
              }
            }

            // User :: Associate Record
            if (Common::validData($p_data, 'sl_no')) {
              $user->assignRole('sales-associate');
              $rnf_status = (Common::validData($p_data, 'rnf_status')) ? SelectRNFStatus::whereTitle($p_data['rnf_status'])->first()->slug : null;

              // Create or Update existing Associate record
              $associate = Associate::updateOrCreate(['lfa_sl_no' => $p_data['sl_no']],
                [
                  'rnf_status_slug' => $rnf_status ?? null,
                  'rnf_no' => $p_data['rnf_no'] ?? null,
                  'aa_code' => $p_data['aa_code'] ?? null,
                  'date_rnf_submission' => Common::parseDate($p_data, 'date_rnf_submission', 'd/m/Y'),
                  'date_rnf_approval' => Common::parseDate($p_data, 'date_rnf_approval', 'd/m/Y'),
                  'date_rnf_withdrawal' => Common::parseDate($p_data, 'date_rnf_withdrawal', 'd/m/Y'),
                  'date_rnf_cessation' => Common::parseDate($p_data, 'date_rnf_cessation', 'd/m/Y'),
                  'date_m9' => Common::parseDate($p_data, 'date_m9', 'd/m/Y'),
                  'date_m9a' => Common::parseDate($p_data, 'date_m9a', 'd/m/Y'),
                  'date_m5' => Common::parseDate($p_data, 'date_m5', 'd/m/Y'),
                  'date_hi' => Common::parseDate($p_data, 'date_hi', 'd/m/Y'),
                  'date_m8' => Common::parseDate($p_data, 'date_m8', 'd/m/Y'),
                  'date_m8a' => Common::parseDate($p_data, 'date_m8a', 'd/m/Y'),
                  'date_cert_ilp' => Common::parseDate($p_data, 'date_cert_ilp', 'd/m/Y'),
                  'date_cert_li' => Common::parseDate($p_data, 'date_cert_li', 'd/m/Y'),
                  'date_cert_fna' => Common::parseDate($p_data, 'date_cert_fna', 'd/m/Y'),
                  'date_cert_bcp' => Common::parseDate($p_data, 'date_bcp', 'd/m/Y'),
                  'date_cert_pgi' => Common::parseDate($p_data, 'date_pgi', 'd/m/Y'),
                  'date_cert_comgi' => Common::parseDate($p_data, 'date_comgi', 'd/m/Y'),
                  'date_cert_cgi' => Common::parseDate($p_data, 'date_cgi', 'd/m/Y'),
                  'cert_pro' => $p_data['cert_pro'] ?? null,
                  'eligible_life' => ($p_data['eligible_life'] == 'YES') ? true : false,
                  'eligible_health' => ($p_data['eligible_health'] == 'YES') ? true : false,
                  'eligible_ilp' => ($p_data['eligible_ilp'] == 'YES') ? true : false,
                  'eligible_cis' => ($p_data['eligible_cis'] == 'YES') ? true : false,
                  'eligible_gi' => ($p_data['eligible_gi'] == 'YES') ? true : false,
                  'eligible_medishield' => ($p_data['eligible_medishield'] == 'YES') ? true : false,
                ]);

              // LFA Associate :: Provider Codes
              $list_provider_codes = [
                'aviva_code',
                'navigator_code',
                'axa_code',
                'ntuc_code',
                'tm_code',
                'fp_code',
                'havenport_code',
                'lic_code',
                'rhi_code',
                'manulife_code',
                'cntaiping_code'
              ];
              foreach ($list_provider_codes as $p_code) {
                if ($p_associate_code = Common::trimString($p_data[$p_code])) {
                  $codes = explode("|", $p_associate_code);
                  foreach ($codes as $code) {
                    if ($code) {
                      $p_provider = SelectProvider::firstAlias(str_replace('_code', '', $p_code));
                      ProviderCode::updateOrCreate([
                        'associate_uuid' => $associate->uuid,
                        'provider_slug' => $p_provider->slug,
                        'code' => ($p_provider->code_length) ? substr($code, 0, $p_provider->code_length) : $code
                      ]);
                    }
                  }
                }
              }

              // Link user to associate record
              $user->sales_associate()->associate($associate)->save();

              // Update user meta
              $user->is_associate = true;
              $user->save();
            } else { // end if $p_data->sl_no
              // Update user meta
              $user->is_associate = false;
              $user->is_staff = true;
              $user->save();
            }
        }
      } // end foreach associate record

      // LFA Associate :: Spouse in LFA
      foreach ($this->spouse_in_lfa as $s_lfa) {
        $user = User::firstUuid($s_lfa['user_uuid']);
        $spouse = Associate::firstSn($s_lfa['spouse_sl_no'])->user->individual;
        $user->spouse()->associate($spouse)->save();
      }


      // LFA Associate :: Movement Records
      $this->command->info('Seeding LFA Associates :: Movement Records');
      $this->associates_movements = json_decode(Storage::get('seeders/associates-movements.json'), true);
      foreach ($this->associates_movements as $p_m_data) {
        if (Common::validData($p_m_data, 'sl_no')) {
          Movement::create([
            'aa_code' => Common::trimString($p_m_data['aa_code']) ?? null,
            'lfa_code' => Common::trimString($p_m_data['lfa_code']) ?? null,
            'associate_uuid' => Associate::firstSn($p_m_data['sl_no'])->uuid,
            'designation_slug' => SelectDesignation::whereTitle($p_m_data['designation'])->first()->slug,
            'reporting_uuid' => (Common::validData($p_m_data, 'reporting_id')) ? Associate::firstSn($p_m_data['reporting_id'])->uuid : null,
            'date_start' => Common::parseDate($p_m_data, 'date_start', 'Y-m-d'),
            'date_end' => Common::parseDate($p_m_data, 'date_end', 'Y-m-d'),
          ]);
        }
      }


      // LFA Associate :: HL Banding Records
      $this->command->info('Seeding LFA Associates :: LFA Banding Records');
      $this->associates_bandings_lfa = json_decode(Storage::get('seeders/associates-bandings-lfa.json'), true);
      foreach ($this->associates_bandings_lfa as $p_b_lfa_data) {
        if (Common::validData($p_b_lfa_data, 'sl_no')) {
          BandingLFA::create([
            'associate_uuid' => Associate::firstSn($p_b_lfa_data['sl_no'])->uuid,
            'date_start' => Common::parseDate($p_b_lfa_data, 'date_start', 'Y-m-d'),
            'date_end' => Common::parseDate($p_b_lfa_data, 'date_end', 'Y-m-d'),
            'banding_type' => $p_b_lfa_data['banding_type'],
            'rank' => $p_b_lfa_data['rank'],
            'rate' => $p_b_lfa_data['rate'],
          ]);
        }
      }

      // LFA Associate :: GI Banding Records
      $this->command->info('Seeding LFA Associates :: General Insurance Banding Records');
      $this->associates_bandings_gi = json_decode(Storage::get('seeders/associates-bandings-gi.json'), true);
      foreach ($this->associates_bandings_gi as $p_b_gi_data) {
        if (Common::validData($p_b_gi_data, 'sl_no')) {
          BandingGI::create([
            'associate_uuid' => Associate::firstSn($p_b_gi_data['sl_no'])->uuid,
            'date_start' => Common::parseDate($p_b_gi_data, 'date_start', 'Y-m-d'),
            'date_end' => Common::parseDate($p_b_gi_data, 'date_end', 'Y-m-d'),
            'rank' => $p_b_gi_data['rank'],
          ]);
        }
      }
    } // end Seeder::Run
} // end Seeder::Class
