<?php

use Carbon\Carbon;
use App\Helpers\PayrollHelper;
use Illuminate\Database\Seeder;
use App\Jobs\PayrollBatchProcessor;

class payroll_2020_02 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payroll_year = '2020';
        $payroll_month = '02';
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
              PayrollHelper::generate_feed('aa', 'rhi', $payroll_year . '-' . $payroll_month . '---aa-bupa.csv', 'bupa', 'bupa'),

              /** ==========================================================
               *  Adjustments Payroll Feeds
               */
              // PayrollHelper::generate_feed('adjustments', 'legacy', $payroll_year . '-' . $payroll_month . '---adjustments.xlsx', 'adjustments'),
              PayrollHelper::generate_feed('adjustments', null, $payroll_year . '-' . $payroll_month . '---additionals.xlsx', 'adjustments'),

              /** ==========================================================
               *  General Insurance Payroll Feeds
               */
              PayrollHelper::generate_feed('gi', 'aviva', $payroll_year . '-' . $payroll_month . '---gi-aviva.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'hlassurance', $payroll_year . '-' . $payroll_month . '---gi-hlassurance.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'liberty', $payroll_year . '-' . $payroll_month . '---gi-liberty.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'msig', $payroll_year . '-' . $payroll_month . '---gi-msig.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'ntuc', $payroll_year . '-' . $payroll_month . '---gi-ntuc.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'qbe', $payroll_year . '-' . $payroll_month . '---gi-qbe.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'sompo', $payroll_year . '-' . $payroll_month . '---gi-sompo.xlsx', 'gi'),

              /** ==========================================================
               *  Legacy FA Payroll Feeds
               */
              PayrollHelper::generate_feed('lfa', 'aviva', $payroll_year . '-' . $payroll_month . '---lfa-aviva-ebh.xlsx', 'ebh'),
              PayrollHelper::generate_feed('lfa', 'aviva', $payroll_year . '-' . $payroll_month . '---lfa-aviva-health-life.xlsx', 'health-life'),
              PayrollHelper::generate_feed('lfa', 'axa', $payroll_year . '-' . $payroll_month . '---lfa-axa-health-life.xlsx', 'health-life', 2),
              PayrollHelper::generate_feed('lfa', 'axa', $payroll_year . '-' . $payroll_month . '---lfa-axa-trailer.xlsx', 'trailer', 2),
              PayrollHelper::generate_feed('lfa', 'cntaiping', $payroll_year . '-' . $payroll_month . '---lfa-cntaiping.xlsx', 'life'),
              PayrollHelper::generate_feed('lfa', 'fp', $payroll_year . '-' . $payroll_month . '---lfa-friends-provident.xlsx', 'life'),
              PayrollHelper::generate_feed('lfa', 'havenport', $payroll_year . '-' . $payroll_month . '---lfa-havenport.xlsx', 'cis'),
              PayrollHelper::generate_feed('lfa', 'navigator', $payroll_year . '-' . $payroll_month . '---lfa-navigator.xlsx', 'cis'),
              PayrollHelper::generate_feed('lfa', 'ntuc', $payroll_year . '-' . $payroll_month . '---lfa-ntuc-esis.xlsx', 'health'),
              PayrollHelper::generate_feed('lfa', 'ntuc', $payroll_year . '-' . $payroll_month . '---lfa-ntuc-life.xlsx', 'life'),
              PayrollHelper::generate_feed('lfa', 'rhi', $payroll_year . '-' . $payroll_month . '---lfa-raffles-health.xlsx', 'health'),
              PayrollHelper::generate_feed('lfa', 'tm', $payroll_year . '-' . $payroll_month . '---lfa-tm-go.xlsx', 'group-override'),
              PayrollHelper::generate_feed('lfa', 'tm', $payroll_year . '-' . $payroll_month . '---lfa-tm-life.xlsx', 'life'),
            ]
        ];

        PayrollBatchProcessor::dispatch($batch_summary)->onQueue('Payroll-Batches');
    }
}
