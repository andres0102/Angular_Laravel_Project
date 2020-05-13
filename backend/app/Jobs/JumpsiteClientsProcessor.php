<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Helpers\{Common, IndividualHelper};
use App\Models\Selections\SelectRace;
use App\Models\LegacyFA\Associates\Associate;
use App\Models\Individuals\{Address, Contact};

class JumpsiteClientsProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $jumpsite_clients;

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
    public function __construct($jumpsite_clients)
    {
        $this->jumpsite_clients = $jumpsite_clients;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      // Begin processing each feed
      foreach ($this->jumpsite_clients as $c_data) {
        if (Common::validData($c_data, 'cll_agent_code')) {
          $c_gname = Common::trimStringUpper($c_data['cll_givenname']);

          if ($c_fname = Common::trimStringUpper($c_data['cll_familyname'])) {
            if (substr($c_fname, 0, 1) == '@' ||
                in_array(substr($c_fname, 0, 2), ['A ', 'B ']) ||
                in_array(substr($c_fname, 0, 3), ['BT ']) ||
                in_array(substr($c_fname, 0, 4), ['BIN ', 'BTE ', 'D/O ', 'S/O ', 'A/L ']) ||
                in_array(substr($c_fname, 0, 6), ['BINTE ', 'BINTI ']) ||
                in_array(substr($c_gname, 0, 2), ['DR', 'MR', 'MS']) ||
                in_array(substr($c_gname, 0, 3), ['MRS']) ||
                in_array(substr($c_gname, 0, 4), ['MOHD', 'MUHD', 'NOOR', 'NOR ', 'NUR ']) ||
                in_array(substr($c_gname, 0, 5), ['ABDUL', 'AHMAD', 'AHMED']) ||
                in_array(substr($c_gname, 0, 7), ['MOHAMAD', 'MOHAMED', 'MUHAMAD', 'MUHAMED']) ||
                in_array(substr($c_gname, 0, 8), ['MOHAMMAD', 'MOHAMMED', 'MUHAMMAD', 'MUHAMMED'])) {
              // SPECIAL :: If Given Name starts with the above, den switch..
              $client_fullname = $c_gname . ' ' . $c_fname;
            } else {
              // DEFAULT :: Full Name = [Family Name] + [Given Name];
              $client_fullname = $c_fname . ' ' . $c_gname;
            }
          } else {
            $client_fullname = $c_gname;
          }
          $client_nric = Common::trimStringUpper($c_data['cll_nric']) ?? null;

          $associate = Associate::firstAaCode($c_data['cll_agent_code']);
          $client = $associate->findOrNewClient($client_fullname, $client_nric);

          if (Common::validData($c_data, 'cll_email') || Common::validData($c_data, 'cll_homephone') || Common::validData($c_data, 'cll_mobilephone')) {
            IndividualHelper::updateContact($client->individual, [
              'home_no' => Common::trimString($c_data['cll_homephone']) ?? null,
              'mobile_no' => Common::trimString($c_data['cll_mobilephone']) ?? null,
              'email' => Common::trimString($c_data['cll_email']) ?? null,
            ]);
          }

          if (Common::validData($c_data, 'block') || Common::validData($c_data, 'street') || Common::validData($c_data, 'unit') || Common::validData($c_data, 'cll_postal_code')) {
            IndividualHelper::updateAddress($client->individual, [
              'block' => Common::trimStringUpper($c_data['block']) ?? null,
              'street' => Common::trimStringUpper($c_data['street']) ?? null,
              'unit' => Common::trimStringUpper($c_data['unit']) ?? null,
              'postal' => Common::trimStringUpper($c_data['cll_postal_code']) ?? null,
            ]);
          }

          $client->individual()->update([
            'date_birth' => Common::parseDate($c_data, 'cll_dob', 'd/m/Y'),
            'race_slug' => Common::validData($c_data, 'cll_race') ? SelectRace::firstOrCreate(['title' => $c_data['cll_race']])->slug : null
          ]);
        } // end if $c_data->cll_agent_code
      } // end foreach client record
    }

    /**
    * Get the tags that should be assigned to the job.
    *
    * @return array
    */
    public function tags() { }
}
