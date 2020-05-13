<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

// Default LFA Associates
use App\Models\LegacyFA\Associates\{Associate};

// Default LFA Payroll
use App\Models\LegacyFA\Payroll\{Instruction};

class data_LfaPayrollInstructions extends Seeder
{
    /**
     * Set default variables.
     *
     */

    private $lfa_payroll_policies = [
      // Angela Bek - AA & LFA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1003', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1003', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1003', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '3', 'from_sl_no' => '1003', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Alvin Nathan - AA & LFA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1001', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1001', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1001', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '3', 'from_sl_no' => '1001', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      // Thomas Ang - AA & LFA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1029', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1029', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1029', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '3', 'from_sl_no' => '1029', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1029', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1029', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1029', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '3', 'from_sl_no' => '1029', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Mike Tang - AA & LFA - T2/T3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1026', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1026', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1026', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '3', 'from_sl_no' => '1026', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1026', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1026', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1026', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '3', 'from_sl_no' => '1026', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Keith Tan - AA & LFA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1036', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1036', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1036', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1036', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Winny Kang - AA & LFA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1025', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1025', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1025', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1025', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Sammi Lee - AA & LFA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1088', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1088', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1088', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1088', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Leong Foo Sheng - AA & LFA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1046', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1046', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1046', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1046', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Lawrence Lim - AA & LFA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1096', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      [ 'era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1096', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1096', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      [ 'era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1096', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Martin Wu - AA & LFA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1022', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1022', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1022', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1022', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Ang Jhing Hun - AA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '5025', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '5025', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Dennis Chan - AA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1032', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1032', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Chee Kok Leong - AA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1015', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1015', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1015', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1015', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Chen Jian Hong - AA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1083', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1083', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1083', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1083', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Chua Ser Chee - AA - T2
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1034', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1034', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Claudin Briigitte - AA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '5051', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '5051', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '5051', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '5051', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Desmond Seah - AA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1028', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1028', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1028', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1028', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Ramli - AA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '5003', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '5003', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '5003', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '5003', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Tay Siang Teck - AA - T2/3
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1038', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1038', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'aa', 'commission_tier' => '2', 'from_sl_no' => '1038', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      ['era' => 'aa', 'commission_tier' => '3', 'from_sl_no' => '1038', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
      // Ng Boon Hwee Luke - LFA - T2
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1167', 'to_sl_no' => '1003', 'date_start' => '2016-01', 'date_end' => '2019-04'],
      ['era' => 'lfa', 'commission_tier' => '2', 'from_sl_no' => '1167', 'to_sl_no' => '1001', 'date_start' => '2019-05', 'date_end' => '2100-12'],
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      // LFA Payroll :: Payroll Policies
      $this->command->info('Seeding LFA Payroll :: Payroll Policies');
      Instruction::truncate();
      foreach ($this->lfa_payroll_policies as $pp_data) {
        Instruction::create([
          'era' => $pp_data['era'],
          'commission_tier' => $pp_data['commission_tier'],
          'from_associate_uuid' => ($pp_data['from_sl_no']) ? Associate::firstSn($pp_data['from_sl_no'])->uuid : NULL,
          'to_associate_uuid' => ($pp_data['to_sl_no']) ? Associate::firstSn($pp_data['to_sl_no'])->uuid : NULL,
          'date_start' => Carbon::parse($pp_data['date_start'])->startOfMonth(),
          'date_end' => Carbon::parse($pp_data['date_end'])->endOfMonth(),
        ]);
      }


    } // end Seeder::Run
} // end Seeder::Class
