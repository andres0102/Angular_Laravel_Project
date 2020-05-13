<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

// Default LFA Selections
use App\Models\Selections\LegacyFA\{SelectProvider, SelectTeamType};

// Default LFA Associates
use App\Models\LegacyFA\Associates\Associate;

// Default LFA Team(s)
use App\Models\LegacyFA\Teams\Team;

// Default LFA Payroll
use App\Models\LegacyFA\Payroll\FirmCode;

class data_LfaAssociatesSeeder extends Seeder
{
    /**
     * Set default variables.
     *
     */
    private $lfa_units = [
      ['unit_code' => 'AB', 'sl_no' => 1003],
      ['unit_code' => 'AJ', 'sl_no' => 1011],
      ['unit_code' => 'AN', 'sl_no' => 1001],
      ['unit_code' => 'AO', 'sl_no' => 1050],
      ['unit_code' => 'CA', 'sl_no' => 1058],
      ['unit_code' => 'CJ', 'sl_no' => 1056],
      ['unit_code' => 'DC', 'sl_no' => 1002],
      ['unit_code' => 'DK', 'sl_no' => 1085],
      ['unit_code' => 'DS', 'sl_no' => 1028],
      ['unit_code' => 'FS', 'sl_no' => 1046],
      ['unit_code' => 'GF', 'sl_no' => 1005],
      ['unit_code' => 'JC', 'sl_no' => 1010],
      ['unit_code' => 'JH', 'sl_no' => 1083],
      ['unit_code' => 'JY', 'sl_no' => 1032],
      ['unit_code' => 'KL', 'sl_no' => 1015],
      ['unit_code' => 'KN', 'sl_no' => 1061],
      ['unit_code' => 'KO', 'sl_no' => 1059],
      ['unit_code' => 'KT', 'sl_no' => 1036],
      ['unit_code' => 'LL', 'sl_no' => 1096],
      ['unit_code' => 'LN', 'sl_no' => 1167],
      ['unit_code' => 'LT', 'sl_no' => 1040],
      ['unit_code' => 'LY', 'sl_no' => 1084],
      ['unit_code' => 'MT', 'sl_no' => 1026],
      ['unit_code' => 'MW', 'sl_no' => 1022],
      ['unit_code' => 'NL', 'sl_no' => 1101],
      ['unit_code' => 'NO', 'sl_no' => 1027],
      ['unit_code' => 'NT', 'sl_no' => 1080],
      ['unit_code' => 'PT', 'sl_no' => 1038],
      ['unit_code' => 'SC', 'sl_no' => 1034],
      ['unit_code' => 'SL', 'sl_no' => 1088],
      ['unit_code' => 'TA', 'sl_no' => 1029],
      ['unit_code' => 'TN', 'sl_no' => 1055],
      ['unit_code' => 'WK', 'sl_no' => 1025],
      ['unit_code' => 'NS', 'sl_no' => 1019],
      ['unit_code' => 'CC', 'sl_no' => 1077],
      ['unit_code' => 'XC', 'sl_no' => 1156],
    ];

    private $lfa_groups = [
      ['group_code' => '00', 'sl_no' => 1001, 'name' => 'Legacy FA Pte Ltd'],
      ['group_code' => '01', 'sl_no' => 1003, 'name' => 'Angela Bek & Associates'],
      ['group_code' => '02', 'sl_no' => 1002, 'name' => 'Darren Chew & Associates'],
      ['group_code' => '03', 'sl_no' => 1005, 'name' => 'Gabriel Francis & Associates'],
      ['group_code' => '05', 'sl_no' => 1015, 'name' => 'Kok Leong & Associates'],
      ['group_code' => '06', 'sl_no' => 1026, 'name' => 'Mike Tang & Associates'],
      ['group_code' => '07', 'sl_no' => 1027, 'name' => 'Nick Ong & Associates'],
      ['group_code' => '08', 'sl_no' => 1028, 'name' => 'Desmond Seah & Associates'],
      ['group_code' => '09', 'sl_no' => 1029, 'name' => 'Thomas Ang & Associates'],
      ['group_code' => '10', 'sl_no' => 1038, 'name' => 'Patrick Tay & Associates'],
      ['group_code' => '11', 'sl_no' => 1055, 'name' => 'Teck Neng & Associates'],
      ['group_code' => '12', 'sl_no' => 1083, 'name' => 'Andy Chen & Associates'],
      ['group_code' => '13', 'sl_no' => 1010, 'name' => 'Jerry Chop & Associates'],
    ];

    private $lfa_payroll_firm_codes = [
      ['provider' => 'ntuc', 'code' => '632900'],
      ['provider' => 'tm', 'code' => '1006070'],
      ['provider' => 'axa', 'code' => '86392']
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      /** ===================================================================================================
       * LFA :: Director Groups
       */
      $this->command->info('Seeding LFA Databases :: Director Groups');
      $this->lfa_team_type_group = SelectTeamType::firstSlug('group')->slug;
      foreach ($this->lfa_groups as $data) {
        $team_code = $data['group_code'];
        Team::firstOrCreate(['code' => $team_code], [
          'type_slug' => $this->lfa_team_type_group,
          'name' => $data['name']
        ]);
      }

      /** ===================================================================================================
       * LFA :: Managerial Units
       */
      $this->command->info('Seeding LFA Databases :: Managerial Units');
      $this->lfa_team_type_unit = SelectTeamType::firstSlug('unit')->slug;
      foreach ($this->lfa_units as $data) {
        $team_code = $data['unit_code'];
        Team::firstOrCreate(['code' => $team_code], ['type_slug' => $this->lfa_team_type_unit]);
      }


      /** ===================================================================================================
       * LFA :: Associate Records
       */
      $this->call(data_CreateUpdateLfaAssociates::class);


      /** ===================================================================================================
       * LFA :: Set Director Groups Ownership
       */
      $this->command->info('Seeding LFA Teams :: Director Groups Ownership');
      foreach ($this->lfa_groups as $data) {
        $team_code = $data['group_code'];
        if ($team_owner = Associate::firstSn($data['sl_no'])) {
          Team::where('type_slug', $this->lfa_team_type_group)->whereCode($team_code)->first()->update([
            'owner_uuid' => $team_owner->uuid,
            // 'name' => "[" . $team_code . "]" . $team_owner->name
          ]);
        }
      }


      /** ===================================================================================================
       * LFA :: Set Managerial Units Ownership
       */
      $this->command->info('Seeding LFA Teams :: Managerial Units Ownership');
      foreach ($this->lfa_units as $data) {
        $team_code = $data['unit_code'];
        if ($team_owner = Associate::firstSn($data['sl_no'])) {
          Team::where('type_slug', $this->lfa_team_type_unit)->whereCode($team_code)->first()->update([
            'owner_uuid' => $team_owner->uuid,
            'name' => "[" . $team_code . "]" . $team_owner->name
          ]);
        }
      }


      /** ===================================================================================================
       * LFA :: Payroll Policies
       */
      $this->call(data_LfaPayrollInstructions::class);


      /** ===================================================================================================
       * LFA :: Payroll Firm Codes
       */
      $this->command->info('Seeding LFA Payroll :: Default Firm Commission Codes');
      foreach ($this->lfa_payroll_firm_codes as $pfc_data) {
        FirmCode::create([
          'provider_slug' => SelectProvider::firstAlias($pfc_data['provider'])->slug,
          'code' => $pfc_data['code'],
        ]);
      }

    } // end Seeder::Run
} // end Seeder::Class
