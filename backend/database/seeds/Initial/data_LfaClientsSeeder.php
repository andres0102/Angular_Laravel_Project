<?php

use Illuminate\Database\Seeder;
use App\Jobs\{JumpsiteClientsProcessor, JumpsitePolicyHoldersProcessor};

class data_LfaClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /** ===================================================================================================
       * JUMPSITE :: Client Records
       */
      $this->command->info('Seeding LFA JUMPSITE :: Client Records');
      $this->jumpsite_clients = json_decode(Storage::get('seeders/jumpsite-clients.json'), true);

      $clients = collect($this->jumpsite_clients)->chunk(2000);
      foreach($clients as $clients_batch) {
        JumpsiteClientsProcessor::dispatch($clients_batch)->onQueue('Payroll-Feeds');
      }

      /** ===================================================================================================
       * JUMPSITE :: Policy Holder Records
       */
      $this->command->info('Seeding LFA JUMPSITE :: Policy Holder Records');
      $this->jumpsite_policyholders = json_decode(Storage::get('seeders/jumpsite-policyholders.json'), true);

      $clients = collect($this->jumpsite_policyholders)->chunk(2000);
      foreach($clients as $clients_batch) {
        JumpsitePolicyHoldersProcessor::dispatch($clients_batch)->onQueue('Payroll-Feeds');
      }

    } // end Seeder::Run
} // end Seeder::Class
