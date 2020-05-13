<?php

use Laravel\Passport\Client;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create Default OAuth2 Client (Password-Grant)
        Client::create([
            'name' => 'LegacyFA API - Password Grant Client',
            'secret' => env('OAUTH_SECRET'),
            'redirect' => env('APP_URL'),
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0
        ]);


        // Default Seeder
        $this->call(data_SelectionsSeeder::class);
        $this->call(data_RolesAndPermissionsSeeder::class);
        $this->call(data_LfaAssociatesSeeder::class);
        $this->call(data_UsersSeeder::class);
        $this->call(data_LfaClientsSeeder::class);
        $this->call(data_ProductsSeeder::class);
        $this->call(data_SubmissionsSeeder::class);
        $this->call(data_NoticesSeeder::class);

        // Payroll Seeder
        // 2016 -- AA Feeds
        $this->call(payroll_2016_01::class);
        $this->call(payroll_2016_02::class);
        $this->call(payroll_2016_03::class);
        $this->call(payroll_2016_04::class);
        $this->call(payroll_2016_05::class);
        $this->call(payroll_2016_06::class);
        $this->call(payroll_2016_07::class);
        $this->call(payroll_2016_08::class);
        $this->call(payroll_2016_09::class);
        $this->call(payroll_2016_10::class);
        $this->call(payroll_2016_11::class);
        $this->call(payroll_2016_12::class);
        // 2017 -- AA Feeds
        $this->call(payroll_2017_01::class);
        $this->call(payroll_2017_02::class);
        $this->call(payroll_2017_03::class);
        $this->call(payroll_2017_04::class);
        $this->call(payroll_2017_05::class);
        $this->call(payroll_2017_06::class);
        $this->call(payroll_2017_07::class);
        $this->call(payroll_2017_08::class);
        $this->call(payroll_2017_09::class);
        $this->call(payroll_2017_10::class);
        $this->call(payroll_2017_11::class);
        $this->call(payroll_2017_12::class);
        // 2018 -- AA & LFA Feeds
        $this->call(payroll_2018_01::class);
        $this->call(payroll_2018_02::class);
        $this->call(payroll_2018_03::class);
        $this->call(payroll_2018_04::class);
        $this->call(payroll_2018_05::class);
        $this->call(payroll_2018_06::class);
        $this->call(payroll_2018_07::class);
        $this->call(payroll_2018_08::class);
        $this->call(payroll_2018_09::class);
        $this->call(payroll_2018_10::class);
        $this->call(payroll_2018_11::class);
        $this->call(payroll_2018_12::class);
        // 2019 -- AA & LFA Feeds
        $this->call(payroll_2019_01::class);
        $this->call(payroll_2019_02::class);
        $this->call(payroll_2019_03::class);
        $this->call(payroll_2019_04::class);
        $this->call(payroll_2019_05::class);
        $this->call(payroll_2019_06::class);
        $this->call(payroll_2019_07::class);
        $this->call(payroll_2019_08::class);
        $this->call(payroll_2019_09::class);
        $this->call(payroll_2019_10::class);
        $this->call(payroll_2019_11::class);
        $this->call(payroll_2019_12::class);
        // 2020 -- AA & LFA Feeds
        $this->call(payroll_2020_01::class);
        $this->call(payroll_2020_02::class);
    }
}
