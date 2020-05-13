<?php

use Carbon\Carbon;
use App\Helpers\PayrollHelper;
use Illuminate\Database\Seeder;
use App\Jobs\PayrollBatchProcessor;

class payroll_2017_06 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payroll_year = '2017';
        $payroll_month = '06';
        $payroll_date = Carbon::parse($payroll_year . '-' . $payroll_month);

        $this->command->info('Starting '. $payroll_year . ' ' . $payroll_date->format("F") .' Payroll Computation...');

        $batch_summary = [
            'batch' => [
              'year' => $payroll_year,
              'month' => $payroll_month
            ],
            'feeds' => [
              /** ==========================================================
               *  Aviva Advisors Payroll Feeds
               */
              PayrollHelper::generate_feed('aa', 'aviva', $payroll_year . '-' . $payroll_month . '---aa-health-basic.csv', 'health', 'basic'),
              PayrollHelper::generate_feed('aa', 'aviva', $payroll_year . '-' . $payroll_month . '---aa-health-or.csv', 'health', 'or'),
              PayrollHelper::generate_feed('aa', 'aviva', $payroll_year . '-' . $payroll_month . '---aa-life-basic.csv', 'life', 'basic'),
              PayrollHelper::generate_feed('aa', 'aviva', $payroll_year . '-' . $payroll_month . '---aa-life-or.csv', 'life', 'or'),
              PayrollHelper::generate_feed('aa', 'aviva', $payroll_year . '-' . $payroll_month . '---aa-trailer.csv', 'trailer', 'trailer'),
              // PayrollHelper::generate_feed('aa', 'rhi', $payroll_year . '-' . $payroll_month . '---aa-bupa.csv', 'bupa', 'bupa'),
            ]
        ];

        PayrollBatchProcessor::dispatch($batch_summary)->onQueue('Payroll-Batches');
    }
}
