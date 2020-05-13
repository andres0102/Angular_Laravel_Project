<?php

use App\Helpers\Common;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Selections\{SelectCountry, SelectGender, SelectResidencyStatus, SelectAddressType, SelectContactType};
use App\Models\Selections\LegacyFA\{SelectPaymentMode, SelectProvider, SelectClientSource, SelectSubmissionCategory, SelectSubmissionStatus, SelectProductCategory};
use App\Models\LegacyFA\Products\{Product, ProductOption, Rider};

use App\Models\LegacyFA\Associates\Associate;
use App\Models\LegacyFA\Clients\{Client, Introducer, Nominee};
use App\Models\LegacyFA\Submissions\{Submission, IntroducerCase};

class data_SubmissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /** ===================================================================================================
       * Introducers
       */
      $this->command->info('Seeding :: LFA Introducers');
      foreach (json_decode(Storage::get('seeders/introducers.json'), true) as $data) {
        $associate = Associate::where('lfa_sl_no', $data['agent_sl_no'])->first();
        if ($associate && Common::validData($data, 'agent_sl_no') && Common::validData($data, 'fullname') && Common::validData($data, 'introducer_id')) {
          $client_name = Common::trimStringUpper($data['fullname']);
          // Create client if dun exists..
          $client = $associate->findOrNewClient($client_name, $data['nric'] ?? null);

          // Create introducer record
          if (Common::validData($data, 'year')) {
            $introducer = $client->introducers()->updateOrCreate(['year' => $data['year']], [
              'associate_uuid' => $associate->uuid,
              'date_start' => $data['date_start'],
              'date_end' => env('CO_LAST_DAY'),
              'gift_received' => (Common::validData($data, 'ticket_1') || Common::validData($data, 'ticket_2')) ?? false,
              'reference_pid' => $data['introducer_id']
            ]);

            if (Common::validData($data, 'ticket_1')) {
              $introducer->gifts()->updateOrCreate(['name' => 'Ticket #1'], ['serial' => $data['ticket_1']]);
            }

            if (Common::validData($data, 'ticket_2')) {
              $introducer->gifts()->updateOrCreate(['name' => 'Ticket #2'], ['serial' => $data['ticket_2']]);
            }
          }

          // Update client individual records...
          $client_individual = $client->individual;

          $client_array = [];
          if (Common::validData($data, 'nric')) $client_array['nric_no'] = Common::trimStringUpper($data['nric']);
          if (Common::validData($data, 'occupation')) $client_array['job_title'] = Common::trimStringUpper($data['occupation']);
          $client_individual->update($client_array);

          $client_contact_array = [];
          if (Common::validData($data, 'contact_number')) $client_contact_array['mobile_no'] = $data['contact_number'];
          if (Common::validData($data, 'email')) $client_contact_array['email'] = $data['email'];
          $client_individual->contacts()->updateOrCreate(['contact_type_slug' => 'default'], $client_contact_array);

          if (Common::validData($data, 'bank_name') && Common::validData($data, 'bank_acc_no')) {
            $bank_name = Common::trimStringUpper($data['bank_name']);
            $bank_slug = null;
            switch (true) {
              case in_array($bank_name, ['ANZ BANK']):
                $bank_slug = "anz-singapore-ltd";
                break;
              case in_array($bank_name, ['BANK OF CHINA']):
                $bank_slug = "bank-of-china-limited";
                break;
              case in_array($bank_name, ['CITI', 'CITIBANK']):
                $bank_slug = "citibank-singapore-limited";
                break;
              case in_array($bank_name, ['DBS', 'DBS BANK', 'DBS LTD', 'DBS MULTIPLIER ACCOUNT', 'DBS SAVINGS', 'DBS SAVINGS PLUS', 'POSB', 'POSB BANK', 'POSB SAVING', 'POSB SAVINGS', 'POSB SAVING ACCOUNT', 'POSB SAVINGS ACC']):
                $bank_slug = "dbs-bank-ltd";
                break;
              case in_array($bank_name, ['HSBC']):
                $bank_slug = "hsbc-bank-singapore-limited";
                break;
              case in_array($bank_name, ['INDIAN BANK']):
                $bank_slug = "indian-bank";
                break;
              case in_array($bank_name, ['MAYBANK', 'MAY BANK']):
                $bank_slug = "malayan-banking-berhad";
                break;
              case in_array($bank_name, ['OCBC', 'OCBC BANK']):
                $bank_slug = "oversea-chinese-banking-corporation-limited";
                break;
              case in_array($bank_name, ['SC', 'STANDARD CHARTERED']):
                $bank_slug = "standard-chartered-bank-singapore-limited";
                break;
              case in_array($bank_name, ['UOB', 'UOB BANK', 'UOB WEALTH BANKING']):
                $bank_slug = "united-overseas-bank-limited";
                break;
            }
            if ($bank_slug) {
              $client_individual->banks()->updateOrCreate(['bank_slug' => $bank_slug], ['account_no' => $data['bank_acc_no']]);
            }
          }
        }
      }


      /** ===================================================================================================
       * Introducers :: Nominees
       */
      $this->command->info('Seeding :: LFA Introducers -- Nominees');
      foreach (json_decode(Storage::get('seeders/nominees.json'), true) as $data) {
        $associate = Associate::where('lfa_sl_no', $data['agent_sl_no'])->first();
        if (Common::validData($data, 'introducer_id') && $introducer = Introducer::firstPid($data['introducer_id'])) {
            $client = $introducer->client;
            if ($associate && $introducer && $client && Common::validData($data, 'nominee_fullname')) {
              $nominee_name = Common::trimStringUpper($data['nominee_fullname']);
              // Create nominee if dun exists..
              $nominee = $introducer->findOrNewNominee($nominee_name);
              // Update nominee data
              if (Common::validData($data, 'nominee_status')) {
                switch ($data['nominee_status']) {
                  case 'N':
                    $nominee_status_slug = 'not-interested';
                    break;
                  case 'OG':
                    $nominee_status_slug = 'on-going-discussions';
                    break;
                  case 'OT':
                    $nominee_status_slug = 'accepted';
                    break;
                  case 'P':
                    $nominee_status_slug = 'pending-contact';
                    break;
                }
              }
              if (Common::validData($data, 'nominee_benefit_option')) {
                switch ($data['nominee_benefit_option']) {
                  case 'B':
                    $nominee_benefit_slug = 'benefit';
                    break;
                  case 'D':
                    $nominee_benefit_slug = 'donate';
                    break;
                  case 'R':
                    $nominee_benefit_slug = 'receive';
                    break;
                }
              }
              $nominee->update([
                'client_uuid' => $client->uuid,
                'associate_uuid' => $associate->uuid,
                'nominee_status_slug' => $nominee_status_slug ?? null,
                'nominee_benefit_slug' => $nominee_benefit_slug ?? null,
                'remarks' => $data['nominee_remarks'] ?? null
              ]);
              // Update nominee individual records...
              if (Common::validData($data, 'nominee_contact_number')) {
                $nominee_individual = $nominee->individual;
                $nominee_individual->contacts()->updateOrCreate(['contact_type_slug' => 'default'], [
                  'mobile_no' => $data['nominee_contact_number']
                ]);
              }
            }
        }
      }


      /** ===================================================================================================
       * Default Submissions
       */
      $this->command->info('Seeding :: LFA Submissions');
      foreach (json_decode(Storage::get('seeders/submission-listings.json'), true) as $data) {
        $associate = Associate::where('lfa_sl_no', $data['sl_no'])->first();
        if ($associate && Common::validData($data, 'sl_no') && Common::validData($data, 'reference_pid') && Common::validData($data, 'client')) {
          $date_submission = Common::parseDate($data, 'date_submission', 'd/m/Y');
          $client_name = Common::trimStringUpper($data['client']);
          // $this->command->info('Processing record (' . $data['reference_pid'] . ') ' . $client_name . ' :: ' . $data['sl_no'] . ' --- ' . $associate->name);

          // Create client if dun exists..
          $client = $associate->findOrNewClient($client_name, ($data['client_nric'] ?? $data['client_nric_2'] ?? null));
          // Update client individual records...
          $client_individual = $client->individual;
          $client_array = [];
          $client_contact_array = [];
          $client_address_array = [];

          if (Common::validData($data, 'client_residency')) {
            if ($data['client_residency'] == "Singaporean") $client_array['residency_status_slug'] = SelectResidencyStatus::whereTitle('Singaporean')->first()->slug;
            else if ($residency_status = SelectResidencyStatus::whereTitle($data['client_residency'])->first()->slug ?? null) $client_array['residency_status_slug'] = $residency_status;
          }
          if (Common::validData($data, 'client_nationality') && $nationality = SelectCountry::whereNationality($data['client_nationality'])->first()->slug ?? null) $client_array['nationality_slug'] = $nationality;
          if (Common::validData($data, 'client_date_birth') && $date_birth = Carbon::createFromFormat('d/m/Y', $data['client_date_birth'])) $client_array['date_birth'] = $date_birth;
          if (Common::validData($data, 'client_gender') && $gender = SelectGender::firstOrCreate(['title' => $data['client_gender']])->slug ?? null) $client_array['gender_slug'] = $gender;
          if (Common::validData($data, 'client_occupation')) $client_array['job_title'] = Common::trimStringUpper($data['client_occupation']);
          if (Common::validData($data, 'client_income_range')) {
            switch ($data['client_income_range']) {
              case '0': $client_array['income_range'] = 0 ; break;
              case '$0 - $29,999': $client_array['income_range'] = 0 ; break;
              case '30': $client_array['income_range'] = 30000 ; break;
              case '$30,000 - $49,999': $client_array['income_range'] = 30000 ; break;
              case '50': $client_array['income_range'] = 50000 ; break;
              case '$50,000 - $99,999': $client_array['income_range'] = 50000 ; break;
              case '100': $client_array['income_range'] = 100000 ; break;
              case '$100,000 - $149,999': $client_array['income_range'] = 100000 ; break;
              case '150': $client_array['income_range'] = 150000 ; break;
              case '$150,000 - $299,999': $client_array['income_range'] = 150000 ; break;
              case '300': $client_array['income_range'] = 300000 ; break;
            }
          }
          if (Common::validData($data, 'client_selected')) $client_array['selected'] = (boolean)($data['client_selected'] === "Yes");
          if (Common::validData($data, 'client_pdpa')) $client_array['pdpa'] = (boolean)($data['client_pdpa'] === "Yes");
          $client_individual->update($client_array);
          if (Common::validData($data, 'client_mobile')) $client_contact_array['mobile_no'] = $data['client_mobile'];
          if (Common::validData($data, 'client_home')) $client_contact_array['home_no'] = $data['client_home'];
          if (Common::validData($data, 'client_email')) $client_contact_array['email'] = $data['client_email'];
          $client_individual->contacts()->updateOrCreate(['contact_type_slug' => 'default'], $client_contact_array);
          if (Common::validData($data, 'client_addr_street')) $client_address_array['street'] = $data['client_addr_street'];
          if (Common::validData($data, 'client_addr_postal')) $client_address_array['postal'] = $data['client_addr_postal'];
          $client_individual->addresses()->updateOrCreate(['address_type_slug' => 'residential'], $client_address_array);

          switch ($data['submission_status']) {
            case 'Verified': $status = SelectSubmissionStatus::firstStep(5); break;
            case 'Submit': $status = SelectSubmissionStatus::firstStep(2); break;
            case 'Rejected': $status = SelectSubmissionStatus::firstStep(0); break;
            default: $status = SelectSubmissionStatus::firstStep(1);
          }

          switch ($data['source']) {
            case 'Existing Client':
              $source = SelectClientSource::firstSlug("client");
              break;
            case 'Referrals':
              $source = SelectClientSource::firstSlug("referrals");
              break;
            case 'Call Centre':
              $source = SelectClientSource::firstSlug("call-center-self");
              break;
            case 'Door Knocking':
              $source = SelectClientSource::firstSlug("door-to-door");
              break;
            case 'Warm':
              $source = SelectClientSource::firstSlug("warm-market");
              break;
            case 'Road Show':
              $source = SelectClientSource::firstSlug("roadshow");
              break;
            case 'Canvassing':
              $source = SelectClientSource::firstSlug("street");
              break;
            default:
              $source = SelectClientSource::firstSlug("others");
          }

          $client->update(['source_slug' => $source->slug]);

          $c_i = $client_individual->fresh();
          $c_i_contact = $c_i->contacts()->where('contact_type_slug', 'default')->first();
          $c_i_address = $c_i->addresses()->where('address_type_slug', 'residential')->first();

          $submission = Submission::updateOrCreate([
            'reference_pid' => $data['reference_pid'],
            'associate_uuid' => $associate->uuid,
          ], [
            'client_uuid' => $client->uuid ?? null,
            'date_submission' => $date_submission,
            'status_slug' => $status->slug ?? null,
            // Snapshots
            'client_type' => 'Individual',
            'client_name' => strtoupper($client->name ?? $client_name ?? null),
            'associate_name' => strtoupper($associate->name),
            'supervisor_name' => strtoupper(($associate->direct_supervisor) ? $associate->direct_supervisor->name : $associate->name),
            'deleted_at' => (Common::validData($data, 'deleted_at')) ? Common::parseDate($data, 'deleted_at', 'd/m/Y') : null,
            // Client Individual Record
            'client_personal' => [
              'salutation_slug' => $c_i->salutation_slug ?? null,
              'full_name' => $c_i->full_name ?? null,
              'alias' => $c_i->alias ?? null,
              'chinese_name' => $c_i->chinese_name ?? null,
              'nric_no' => $c_i->nric_no ?? null,
              'fin_no' => $c_i->fin_no ?? null,
              'passport_no' => $c_i->passport_no ?? null,
              'gender_slug' => $c_i->gender_slug ?? null,
              'date_birth' => $c_i->date_birth ?? null,
              'race_slug' => $c_i->race_slug ?? null,
              'country_birth_slug' => $c_i->country_birth_slug ?? null,
              'nationality_slug' => $c_i->nationality_slug ?? null,
              'residency_status_slug' => $c_i->residency_status_slug ?? null,
              'marital_status_slug' => $c_i->marital_status_slug ?? null,
              'employment_status_slug' => $c_i->employment_status_slug ?? null,
              'income_range' => $c_i->income_range ?? null,
              'job_title' => $c_i->job_title ?? null,
              'company_name' => $c_i->company_name ?? null,
              'business_nature' => $c_i->business_nature ?? null,
              'education_level_slug' => $c_i->education_level_slug ?? null,
              'education_institution' => $c_i->education_institution ?? null,
              'field_of_study' => $c_i->field_of_study ?? null,
              'smoker' => $c_i->smoker ?? null,
              'selected' => $c_i->selected ?? null,
              'pdpa' => $c_i->pdpa ?? null,
              'contact_information' => [
                'home_no' => ($c_i_contact) ? $c_i_contact->home_no : null,
                'mobile_no' => ($c_i_contact) ? $c_i_contact->mobile_no : null,
                'fax_no' => ($c_i_contact) ? $c_i_contact->fax_no : null,
                'email' => ($c_i_contact) ? $c_i_contact->email : null,
              ],
              'address_information' => [
                'block' => ($c_i_address) ? $c_i_address->block : null,
                'street' => ($c_i_address) ? $c_i_address->street : null,
                'unit' => ($c_i_address) ? $c_i_address->unit : null,
                'building' => ($c_i_address) ? $c_i_address->building : null,
                'city' => ($c_i_address) ? $c_i_address->city : null,
                'postal' => ($c_i_address) ? $c_i_address->postal : null,
                'country_slug' => ($c_i_address) ? $c_i_address->country_slug : null,
              ]
            ]
          ]);

          // Submission - Sub Item
          $provider = (Common::validData($data, 'provider') && SelectProvider::firstAlias($data['provider'])) ? SelectProvider::firstAlias($data['provider']) : null;
          if ($submission_cat = (Common::validData($data, 'type') && SelectSubmissionCategory::firstSlug($data['type'])->exists()) ? SelectSubmissionCategory::firstSlug($data['type']) : null) {
              if ($data['type'] == 'insurance') {
                  $option = (Common::validData($data, 'plan_id')) ? ProductOption::where('reference_pid', $data['plan_id'])->first() : null;
                  $product = ($option) ? $option->product : null;
                  $product_cat = ($option) ? $option->category : null;
              } else if ($data['type'] == 'cis') {
                  $product_option = (Common::validData($data, 'cis_uen')) ? ProductOption::where('reference_uen', Str::slug($data['cis_uen']))->first() : null;
                  $product = ($product_option) ? $product_option->product : null;
                  $product_cat = SelectProductCategory::firstSlug('cis');
              } else if ($data['type'] == 'gi') {
                  $product_cat = (Common::validData($data, 'gi_product_cat')) ? SelectProductCategory::firstSlug($data['gi_product_cat']) : SelectProductCategory::firstSlug('gi-others');
              } else if ($data['type'] == 'loans') {
                  $product_cat = SelectProductCategory::firstSlug('loans');
              } else if ($data['type'] == 'wills') {
                  $product_cat = SelectProductCategory::firstSlug('wills');
              }
          }

          // snapshot names
          $product_name = ($product) ? $product->name : null;
          $option_name = ($option) ? $option->name : null;
          $product_cat_name = ($product_cat) ? $product_cat->title : null;

          switch ($data['payment_mode']) {
            case 'Cheque': $payment_mode = SelectPaymentMode::firstSlug("cheque"); break;
            case 'CPF': $payment_mode = SelectPaymentMode::firstSlug("cpf"); break;
            case 'Credit Card': $payment_mode = SelectPaymentMode::firstSlug("credit-card"); break;
            case 'Direct Transfer': $payment_mode = SelectPaymentMode::firstSlug("direct-debit"); break;
            case 'GIRO Payment': $payment_mode = SelectPaymentMode::firstSlug("giro"); break;
            case 'SRS': $payment_mode = SelectPaymentMode::firstSlug("srs"); break;
            default: $payment_mode = SelectPaymentMode::firstSlug("others");
          }

          // Create life_assured if dun exists..
          // For now, temporary leave life_assured as blank..
          $life_assured = $la_name = $la_individual = null;
          if (Common::validData($data, 'la_name') || $data['la_is_client'] === 'yes') {
            $life_assured = ($data['la_is_client'] === 'yes') ? $client->fresh() : $client->findOrNewLifeAssured($data['la_name'], $data['la_nric'] ?? null);
            //
            if ($life_assured->is($client)) $data['la_is_client'] = 'yes';

            $la_name = ($data['la_is_client'] !== 'yes') ? Common::trimStringUpper($data['la_name']) : null;
            // Update life assured individual records...
            $la_individual = $life_assured->individual;
            $la_array = [];
            $la_contact_array = [];
            $la_address_array = [];

            if (Common::validData($data, 'la_residency')) {
              if ($data['la_residency'] == "Singaporean") $la_array['residency_status_slug'] = SelectResidencyStatus::whereTitle('Singaporean')->first()->slug;
              else if ($residency_status = SelectResidencyStatus::whereTitle($data['la_residency'])->first()->slug ?? null) $la_array['residency_status_slug'] = $residency_status;
            }
            if (Common::validData($data, 'la_nationality') && $nationality = SelectCountry::whereNationality($data['la_nationality'])->first()->slug ?? null) $la_array['nationality_slug'] = $nationality;
            if (Common::validData($data, 'la_date_birth') && $date_birth = Carbon::createFromFormat('d/m/Y', $data['la_date_birth'])) $la_array['date_birth'] = $date_birth;
            if (Common::validData($data, 'la_gender') && $gender = SelectGender::firstOrCreate(['title' => $data['la_gender']])->slug ?? null) $la_array['gender_slug'] = $gender;
            if (Common::validData($data, 'la_occupation')) $la_array['job_title'] = Common::trimStringUpper($data['la_occupation']);
            if (Common::validData($data, 'la_income_range')) {
              switch ($data['la_income_range']) {
                case '0': $la_array['income_range'] = 0 ; break;
                case '$0 - $29,999': $la_array['income_range'] = 0 ; break;
                case '30': $la_array['income_range'] = 30000 ; break;
                case '$30,000 - $49,999': $la_array['income_range'] = 30000 ; break;
                case '50': $la_array['income_range'] = 50000 ; break;
                case '$50,000 - $99,999': $la_array['income_range'] = 50000 ; break;
                case '100': $la_array['income_range'] = 100000 ; break;
                case '$100,000 - $149,999': $la_array['income_range'] = 100000 ; break;
                case '150': $la_array['income_range'] = 150000 ; break;
                case '$150,000 - $299,999': $la_array['income_range'] = 150000 ; break;
                case '300': $la_array['income_range'] = 300000 ; break;
              }
            }
            $la_individual->update($la_array);
            if (Common::validData($data, 'la_mobile')) $la_contact_array['mobile_no'] = $data['la_mobile'];
            if (Common::validData($data, 'la_home')) $la_contact_array['home_no'] = $data['la_home'];
            if (Common::validData($data, 'la_email')) $la_contact_array['email'] = $data['la_email'];
            $la_individual->contacts()->updateOrCreate(['contact_type_slug' => 'default'], $la_contact_array);
            if (Common::validData($data, 'la_addr_street')) $la_address_array['street'] = $data['la_addr_street'];
            if (Common::validData($data, 'la_addr_postal')) $la_address_array['postal'] = $data['la_addr_postal'];
            $la_individual->addresses()->updateOrCreate(['address_type_slug' => 'residential'], $la_address_array);
          }

          $l_a_i = (Common::validData($life_assured)) ? $la_individual->fresh() : null;
          $l_a_i_contact = (Common::validData($life_assured)) ? $l_a_i->contacts()->where('contact_type_slug', 'default')->first() : null;
          $l_a_i_address = (Common::validData($life_assured)) ? $l_a_i->addresses()->where('address_type_slug', 'residential')->first() : null;

          // Calculate Premiums
          $gst = (float) $data['gst'];
          $premium_after = (float) $data['premium'];
          $premium_before = (float) $data['premium_before'];
          $gst_rate = 0;

          if ($gst > 0) {
            // There is GST
            $gst_rate = 0.07;
            if ($premium_after == $premium_before) {
              $premium_before = $premium_after - $gst;
            } else if ($premium_after < $premium_before) {
              $premium_after = $premium_before + $gst;
            } else if ($premium_after > $premium_before) {
              // Do nothing.
            }
          } else {
            // There is no GST
            if ($premium_after == $premium_before) {
              // Do nothing.
            } else if ($premium_after < $premium_before) {
              $premium_after = $premium_before;
            } else if ($premium_after > $premium_before && $premium_before != 0) {
              // GST is apparently being calculated. There may be GST
              $gst = $premium_after - $premium_before;
              $gst_rate = 0.07;
            } else if ($premium_before == 0) {
              $premium_before = $premium_after;
            }
          }

          $case = $submission->cases()->updateOrCreate([
            'associate_uuid' => $associate->uuid,
          ], [
            'client_uuid' => $client->uuid ?? null,
            'life_assured_uuid' => (Common::validData($life_assured) && $data['la_is_client'] !== 'yes') ? $life_assured->uuid : null,
            'provider_slug' => ($provider) ? $provider->slug : null,
            'submission_cat_slug' => $submission_cat->slug,
            'product_cat_slug' => ($product_cat) ? $product_cat->slug : null,
            'product_uuid' => ($product) ? $product->uuid : null,
            'product_option_uuid' => ($option) ? $option->uuid : null,
            // Snapshot Values
            'currency' => $data['currency'] ?? null,
            'ape' => (float) $data['ape'] ?? 0,
            'provider_name' => ($provider) ? $provider->full_name : null,
            'life_assured_name' => ($data['la_is_client'] !== 'yes') ? $la_name : null,
            'life_assured_is_client' => ($data['la_is_client'] === 'yes') ? true : false,
            // Life Assured Individual Record
            'life_assured_personal' => (Common::validData($l_a_i) && $data['la_is_client'] !== 'yes') ? [
              'salutation_slug' => $l_a_i->salutation_slug ?? null,
              'full_name' => $l_a_i->full_name ?? null,
              'alias' => $l_a_i->alias ?? null,
              'chinese_name' => $l_a_i->chinese_name ?? null,
              'nric_no' => $l_a_i->nric_no ?? null,
              'fin_no' => $l_a_i->fin_no ?? null,
              'passport_no' => $l_a_i->passport_no ?? null,
              'gender_slug' => $l_a_i->gender_slug ?? null,
              'date_birth' => $l_a_i->date_birth ?? null,
              'race_slug' => $l_a_i->race_slug ?? null,
              'country_birth_slug' => $l_a_i->country_birth_slug ?? null,
              'nationality_slug' => $l_a_i->nationality_slug ?? null,
              'residency_status_slug' => $l_a_i->residency_status_slug ?? null,
              'marital_status_slug' => $l_a_i->marital_status_slug ?? null,
              'employment_status_slug' => $l_a_i->employment_status_slug ?? null,
              'income_range' => $l_a_i->income_range ?? null,
              'job_title' => $l_a_i->job_title ?? null,
              'company_name' => $l_a_i->company_name ?? null,
              'business_nature' => $l_a_i->business_nature ?? null,
              'education_level_slug' => $l_a_i->education_level_slug ?? null,
              'education_institution' => $l_a_i->education_institution ?? null,
              'field_of_study' => $l_a_i->field_of_study ?? null,
              'smoker' => $l_a_i->smoker ?? null,
              'selected' => $l_a_i->selected ?? null,
              'pdpa' => $l_a_i->pdpa ?? null,
              'contact_information' => [
                'home_no' => ($l_a_i_contact) ? $l_a_i_contact->home_no : null,
                'mobile_no' => ($l_a_i_contact) ? $l_a_i_contact->mobile_no : null,
                'fax_no' => ($l_a_i_contact) ? $l_a_i_contact->fax_no : null,
                'email' => ($l_a_i_contact) ? $l_a_i_contact->email : null,
              ],
              'address_information' => [
                'block' => ($l_a_i_address) ? $l_a_i_address->block : null,
                'street' => ($l_a_i_address) ? $l_a_i_address->street : null,
                'unit' => ($l_a_i_address) ? $l_a_i_address->unit : null,
                'building' => ($l_a_i_address) ? $l_a_i_address->building : null,
                'city' => ($l_a_i_address) ? $l_a_i_address->city : null,
                'postal' => ($l_a_i_address) ? $l_a_i_address->postal : null,
                'country_slug' => ($l_a_i_address) ? $l_a_i_address->country_slug : null,
              ]
            ] : null,
            'submission_category' => $submission_cat->title ?? null,
            'product_category' => $product_cat_name ?? null,
            'product_name' => $product_name ?? null,
            'option_name' => $option_name ?? null,
            'investment_account_type' => (Common::validData($data, 'cis_account')) ? Str::slug($data['cis_account']) : null,
            'investment_transaction_type' => (Common::validData($data, 'cis_transaction')) ? Str::slug($data['cis_transaction']) : null,
            'loan_property_address' => (Common::validData($data, 'loan_address')) ? $data['loan_address'] : null,
            'loan_amount' => (float) $data['loan_amount'] ?? 0,
            'loan_consent' => (Common::validData($data, 'loan_consent')) ? Str::slug($data['loan_consent']) : null,
            'policy_term' => (float) $data['policy_term'] ?? 0,
            'sum_assured' => (float) $data['sum_assured'] ?? 0,
            'payment_term' => (float) $data['payment_term'] ?? 0,
            'payment_frequency' => $data['frequency'] ?? 0,
            'payment_type' => (Common::validData($data, 'frequency') && $data['frequency'] == 'single') ? 'single' : 'regular',
            'gross_payment_before_gst' => $premium_before,
            'gross_payment_gst' => $gst,
            'gross_payment_after_gst' => $premium_after,
            'payment_discount' => 0,
            'nett_payment_before_gst' => $premium_before,
            'nett_payment_gst' => $gst,
            'nett_payment_after_gst' => $premium_after,
            'gst_rate' => $gst_rate,
            'payment_mode_slug' => ($payment_mode) ? $payment_mode->slug ?? null : null,
            'payment_mode' => ($payment_mode) ? $payment_mode->title ?? null : null,
            'submission_mode' => $data['submission_mode'] ?? 0,
            'reference_no' => $data['reference_pid']
          ]);

          // Record down additional information/coverage tagged to this case
          if($submission_cat) {
              if ($data['type'] == 'insurance') {
                  // if (Common::validData($data, 'policy_term')) $case->info()->updateOrCreate(['title' => 'insurance_policy_term'], ['value' => $data['policy_term']]);
                  // if (Common::validData($data, 'sum_assured')) $case->info()->updateOrCreate(['title' => 'insurance_sum_assured'], ['value' => $data['sum_assured']]);
              } else if ($data['type'] == 'cis') {
                  // if (Common::validData($data, 'cis_account')) $case->info()->updateOrCreate(['title' => 'investment_account_type'], ['value' => Str::slug($data['cis_account'])]);
                  // if (Common::validData($data, 'cis_transaction')) $case->info()->updateOrCreate(['title' => 'investment_transaction_type'], ['value' => Str::slug($data['cis_transaction'])]);
              } else if ($data['type'] == 'gi') {
              } else if ($data['type'] == 'loans') {
                  // if (Common::validData($data, 'loan_address')) $case->info()->updateOrCreate(['title' => 'loan_property_address'], ['value' => $data['loan_address']]);
                  // if (Common::validData($data, 'loan_consent')) $case->info()->updateOrCreate(['title' => 'loan_consent'], ['value' => Str::slug($data['loan_consent'])]);
                  if (Common::validData($data, 'doc_consent_original')) $case->info()->updateOrCreate(['title' => 'loan_doc_consent_original'], ['value' => Str::slug($data['doc_consent_original'])]);
                  if (Common::validData($data, 'doc_consent')) $case->info()->updateOrCreate(['title' => 'loan_doc_consent'], ['value' => Str::slug($data['doc_consent'])]);
              } else if ($data['type'] == 'wills') { }
          }

          if (Common::validData($data, 'doc_pfr_original')) $case->info()->updateOrCreate(['title' => 'doc_pfr_original'], ['value' => $data['doc_pfr_original']]);
          if (Common::validData($data, 'doc_pfr')) $case->info()->updateOrCreate(['title' => 'doc_pfr'], ['value' => $data['doc_pfr']]);
          if (Common::validData($data, 'doc_app_original')) $case->info()->updateOrCreate(['title' => 'doc_app_original'], ['value' => $data['doc_app_original']]);
          if (Common::validData($data, 'doc_app')) $case->info()->updateOrCreate(['title' => 'doc_app'], ['value' => $data['doc_app']]);
          if (Common::validData($data, 'doc_nric_original')) $case->info()->updateOrCreate(['title' => 'doc_nric_original'], ['value' => $data['doc_nric_original']]);
          if (Common::validData($data, 'doc_nric')) $case->info()->updateOrCreate(['title' => 'doc_nric'], ['value' => $data['doc_nric']]);
          if (Common::validData($data, 'doc_bi_original')) $case->info()->updateOrCreate(['title' => 'doc_bi_original'], ['value' => $data['doc_bi_original']]);
          if (Common::validData($data, 'doc_bi')) $case->info()->updateOrCreate(['title' => 'doc_bi'], ['value' => $data['doc_bi']]);
          if (Common::validData($data, 'doc_supporting_original')) $case->info()->updateOrCreate(['title' => 'doc_supporting_original'], ['value' => $data['doc_supporting_original']]);
          if (Common::validData($data, 'doc_supporting')) $case->info()->updateOrCreate(['title' => 'doc_supporting'], ['value' => $data['doc_supporting']]);
          if (Common::validData($data, 'doc_others_original')) $case->info()->updateOrCreate(['title' => 'doc_others_original'], ['value' => $data['doc_others_original']]);
          if (Common::validData($data, 'doc_others')) $case->info()->updateOrCreate(['title' => 'doc_others'], ['value' => $data['doc_others']]);

          // Tag submission case to introducer record
          if (Common::validData($data, 'introducer_id') && $introducer = Introducer::firstPid($data['introducer_id'])) {
            $introducer_case_primary = [
              'submission_uuid' => $submission->uuid,
              'case_uuid' => $case->uuid,
              'associate_uuid' => $associate->uuid,
              'client_uuid' => $client->uuid,
              'life_assured_uuid' => (Common::validData($life_assured) && $data['la_is_client'] !== 'yes') ? $life_assured->uuid : null,
              'introducer_uuid' => $introducer->uuid,
            ];

            $introducer_case_snapshot = [
              'associate_name' => $associate->name,
              'client_name' => $client->name,
              'client_nric_no' => $client->individual->nric_no,
              'life_assured_name' => (Common::validData($life_assured) && $data['la_is_client'] !== 'yes') ? $life_assured->name : null,
              'life_assured_is_client' => ($data['la_is_client'] === 'yes') ? true : false,
              'introducer_name' => $introducer->name,
              'provider_name' => $case->provider_name,
              'submission_category' => $case->submission_category,
              'product_category' => $case->product_category,
              'product_name' => $case->product_name,
              'option_name' => $case->option_name,
              'payment_term' => $case->payment_term,
              'payment_frequency' => $case->payment_frequency,
              'payment_type' => $case->payment_type,
              // 'payment' => $case->payment,
              // 'payment_gst' => $case->payment_gst,
              'gst_rate' => $case->gst_rate,
              'gross_payment_before_gst' => $case->gross_payment_before_gst,
              'gross_payment_gst' => $case->gross_payment_gst,
              'gross_payment_after_gst' => $case->gross_payment_after_gst,
              'payment_discount' => $case->payment_discount,
              'nett_payment_before_gst' => $case->nett_payment_before_gst,
              'nett_payment_gst' => $case->nett_payment_gst,
              'nett_payment_after_gst' => $case->nett_payment_after_gst,
              'payment_mode' => $case->payment_mode,
              'currency' => $case->currency,
              'ape' => $case->ape
            ];

            /**
             * Determine introducer scheme type :: Introducer / Nominee / Charity
             * 1) Check if $introducer_name == $submission->client_name (Scheme type: Introducer)
             * 2) Check if $introducer_name <> $submission->client_name (Scheme type: Nominee)
            *  --- If Nominee, check benefit
            **/
            $submission_client_name = $submission->client_name;
            $introducer_name = $introducer->client->name;

            if ($introducer_name == $submission_client_name) {
              // Policyholder is Introducer
              // Receipient == Introducer
              $bank_info = $introducer->client->individual->banks->first();
              $introducer_additional_array = [
                'introducer_scheme_type' => 'introducer',
                'scheme_receiver_type' => 'introducer',
                'scheme_receiver_name' => $introducer->name,
                'scheme_bank_name' => ($bank_info) ? $bank_info->bank->full_name : null,
                'scheme_bank_account_no' => ($bank_info) ? $bank_info->account_no : null,
              ];

            } else {
              // Policyholder is not Introducer (One of the Nominees)
              // Recipient == Nominee status benefit (Default = Nominee)
              // Create nominee if dun exists..
              $nominee = $introducer->findOrNewNominee($client_individual->full_name, $client_individual->nric_no ?? null);
              $nominee->update(['converted' => true]);
              switch ($nominee->nominee_benefit_slug) {
                case 'receive':
                  $receiver_type = 'introducer';
                  $receiver_name = $introducer->name;
                  break;
                case 'donate':
                  $receiver_type = 'charity';
                  $receiver_name = null;
                  break;
                default:
                  $receiver_type = 'nominee';
                  $receiver_name = $client_individual->full_name;
              }

              $bank_info = $nominee->individual->banks->first();
              $introducer_additional_array = [
                'introducer_scheme_type' => 'nominee',
                'nominee_uuid' => $nominee->uuid,
                'nominee_name' => $client_individual->full_name,
                'scheme_receiver_type' => $receiver_type,
                'scheme_receiver_name' => $receiver_name,
                'scheme_bank_name' => ($bank_info) ? $bank_info->bank->full_name : null,
                'scheme_bank_account_no' => ($bank_info) ? $bank_info->account_no : null,
              ];
            }

            $introducer_case = IntroducerCase::updateOrCreate($introducer_case_primary, array_merge($introducer_case_snapshot, $introducer_additional_array));
          }

        }
      }

      $this->command->info('Seeding :: LFA Submissions - Riders');
      foreach (json_decode(Storage::get('seeders/submission-riders.json'), true) as $data) {
          // Get Submission
        if (Common::validData($data, 'sub_pid') && Common::validData($data, 'rider_pid')) {
          $submission_record = Submission::firstPid($data['sub_pid']);
          $rider_record = Rider::firstPid($data['rider_pid']);
          if ($submission_record && $rider_record) {
              $case_record = $submission_record->cases()->first();
              if ($case_record) {
                  // Delete duplicated instances ...
                  $case_record->riders()->detach($rider_record);
                  // Attach fresh new instance ...
                  $case_record->riders()->attach($rider_record, [
                    'rider_name' => $rider_record->name,
                    'sum_assured' => $data['sum_assured'],
                    'policy_term' => $case_record->policy_term,
                    'payment_term' => $case_record->payment_term,
                    'gross_payment_after_gst' => $data['premium'] ?? 0,
                    'nett_payment_after_gst' => $data['premium'] ?? 0,
                  ]);
              }
          }
        }
      }

    }
}