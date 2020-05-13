<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Users\{User};
use App\Models\Individuals\Individual;
use App\Models\LegacyFA\Associates\Associate;

class data_UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::updateOrCreate(['email' => 'xavier@legacyfa-asia.com'],
        [
          'password' => bcrypt('12345'),
          'designation_slug' => 'chief-technology-officer-cto'
        ]);

        $individual = Individual::updateOrCreate([
          'full_name' => 'Xavier J. Wong'
        ], [
          'gender_slug' => 'male',
        ]);

        $user->individual_uuid = $individual->uuid;
        $user->save();
        $user->assignRole('super-admin');

        //

        $user_maisarah = User::updateOrCreate(['email' => 'nur.maisarah.hq@legacyfa-asia.com'],
        [
          'password' => bcrypt('12345'),
          'is_staff' => true,
          'designation_slug' => 'admin-executive'
        ]);

        $individual_maisarah = Individual::updateOrCreate([
          'full_name' => 'Nur Maisarah Binte Abdul Samnd'
        ], [
          'gender_slug' => 'female',
        ]);

        $user_maisarah->individual_uuid = $individual_maisarah->uuid;
        $user_maisarah->save();
        $user_maisarah->assignRole('hq-staff');

        //

        $user_leo = User::updateOrCreate(['email' => 'leo.ys.hq@legacyfa-asia.com'],
        [
          'password' => bcrypt('12345'),
          'is_staff' => true,
          'designation_slug' => 'it-manager'
        ]);

        $individual_leo = Individual::updateOrCreate([
          'full_name' => 'Leo Yulianto Suryatejoisworo'
        ], [
          'gender_slug' => 'male',
        ]);

        $user_leo->individual_uuid = $individual_leo->uuid;
        $user_leo->save();
        $user_leo->assignRole('hq-staff');

        //

        $user_bobby = User::updateOrCreate(['email' => 'bobby.chew.hq@legacyfa-asia.com'],
        [
          'password' => bcrypt('12345'),
          'is_staff' => true,
          'designation_slug' => 'admin-executive'
        ]);

        $individual_bobby = Individual::updateOrCreate([
          'full_name' => 'Bobby Chew'
        ], [
          'gender_slug' => 'male',
        ]);

        $user_bobby->individual_uuid = $individual_bobby->uuid;
        $user_bobby->save();
        $user_bobby->assignRole('hq-staff');

        Associate::active()->filter(function($item){
          return $item->is_manager;
        })->transform(function($item){
          return $item->user;
        })->each(function($item){
          return $item->assignRole('sales-manager');
        });
    }
}
