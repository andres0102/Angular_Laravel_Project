<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Helpers\{Common, IndividualHelper};
use App\Models\Selections\{SelectGender, SelectRace};
use App\Models\LegacyFA\Associates\Associate;
use App\Models\Individuals\{Address, Contact};

class JumpsitePolicyHoldersProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jumpsite_policyholders;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jumpsite_policyholders)
    {
        $this->jumpsite_policyholders = $jumpsite_policyholders;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Begin processing each feed
        foreach ($this->jumpsite_policyholders as $c_data) {
        if (Common::validData($c_data, 'policy_holder_name')) {
          $client_fullname = Common::trimStringUpper($c_data['policy_holder_name']);
          $client_nric = Common::trimStringUpper($c_data['policy_holder_nric']) ?? null;
          $associate = Associate::firstAaCode($c_data['agent_no']);
          $client = $associate->findOrNewClient($client_fullname, $client_nric);

          if (Common::validData($c_data, 'policy_holder_email') || Common::validData($c_data, 'policy_holder_mobile')) {
            IndividualHelper::updateContact($client->individual, [
              'mobile_no' => Common::trimString($c_data['policy_holder_mobile']) ?? null,
              'email' => Common::trimString($c_data['policy_holder_email']) ?? null,
            ]);
          }

          if (Common::validData($c_data, 'policy_holder_address') || Common::validData($c_data, 'policy_holder_postalcode')) {
            IndividualHelper::updateAddress($client->individual, [
              'street' => Common::trimStringUpper($c_data['policy_holder_address']) ?? null,
              'postal' => Common::trimStringUpper($c_data['policy_holder_postalcode']) ?? null,
            ]);
          }

          $client->individual()->update([
            'date_birth' => Common::parseDate($c_data, 'policy_holder_dob', 'd/m/Y'),
            'job_title' => $c_data['policy_holder_jobtitle'] ?? null,
            'gender_slug' => Common::validData($c_data, 'policy_holder_gender') ? SelectGender::firstOrCreate(['title' => $c_data['policy_holder_gender']])->slug : null
          ]);

          $life_assured_fullname = Common::trimStringUpper($c_data['life_assured_name']);
          $life_assured_nric = Common::trimStringUpper($c_data['life_assured_nric']);
          $life_assured = $client->findOrNewLifeAssured($life_assured_fullname, $life_assured_nric);

          $life_assured->individual()->update([
            'date_birth' => Common::parseDate($c_data, 'life_assured_dob', 'd/m/Y'),
            'job_title' => $c_data['life_assured_jobtitle'] ?? null,
            'gender_slug' => Common::validData($c_data, 'life_assured_gender') ? SelectGender::firstOrCreate(['title' => $c_data['life_assured_gender']])->slug : null
          ]);



        } // end if $c_data['cll_agent_code
      } // end foreach client record
    }

    /**
    * Get the tags that should be assigned to the job.
    *
    * @return array
    */
    public function tags() { }
}
