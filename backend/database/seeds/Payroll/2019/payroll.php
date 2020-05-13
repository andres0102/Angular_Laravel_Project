<?php

use Carbon\Carbon;
use App\Helpers\PayrollHelper;
use Illuminate\Database\Seeder;
use App\Jobs\PayrollBatchProcessor;
use App\Models\Selections\LegacyFA\Provider;
use App\Models\LegacyFA\Payroll\{PayrollBatch, PayrollFeed};
use App\Models\Selections\LegacyFA\{SelectPayrollFeedType, SelectProvider};

use Rap2hpoutre\FastExcel\FastExcel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Exceptions\UnreadableFileException;
use App\Imports\{PayrollFeedsImport, PayrollFeedsImportCsvPipe};

class payroll extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payroll_year = '2019';
        $payroll_month = '07';
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
              PayrollHelper::generate_feed('aa', 'rhi', $payroll_year . '-' . $payroll_month . '---aa-bupa.csv', 'bupa', 'bupa'),

              /** ==========================================================
               *  Adjustments Payroll Feeds
               */
              PayrollHelper::generate_feed('adjustments', 'legacy', $payroll_year . '-' . $payroll_month . '---adjustments.xlsx', 'adjustments'),

              /** ==========================================================
               *  General Insurance Payroll Feeds
               */
              PayrollHelper::generate_feed('gi', 'aig', $payroll_year . '-' . $payroll_month . '---gi-aig.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'aviva', $payroll_year . '-' . $payroll_month . '---gi-aviva.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'axa', $payroll_year . '-' . $payroll_month . '---gi-axa.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'chubb', $payroll_year . '-' . $payroll_month . '---gi-chubb.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'liberty', $payroll_year . '-' . $payroll_month . '---gi-liberty.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'msig', $payroll_year . '-' . $payroll_month . '---gi-msig.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'ntuc', $payroll_year . '-' . $payroll_month . '---gi-ntuc.xlsx', 'gi'),
              PayrollHelper::generate_feed('gi', 'sompo', $payroll_year . '-' . $payroll_month . '---gi-sompo.xlsx', 'gi'),

              /** ==========================================================
               *  Legacy FA Payroll Feeds
               */
              // [
              //     'era' => 'lfa',
              //     'filename' => '2019-07---lfa-aviva-ebh.xlsx',
              //     'provider' => Provider::hasAlias('aviva'),
              //     'category' => 'ebh'
              // ],
              PayrollHelper::generate_feed('lfa', 'aviva', $payroll_year . '-' . $payroll_month . '---lfa-aviva-health-life.xlsx', 'health-life'),
              PayrollHelper::generate_feed('lfa', 'axa', $payroll_year . '-' . $payroll_month . '---lfa-axa-health-life.xlsx', 'health-life', 2),
              PayrollHelper::generate_feed('lfa', 'axa', $payroll_year . '-' . $payroll_month . '---lfa-axa-trailer.xlsx', 'trailer', 2),
              PayrollHelper::generate_feed('lfa', 'fp', $payroll_year . '-' . $payroll_month . '---lfa-friends-provident.xlsx', 'life'),
              PayrollHelper::generate_feed('lfa', 'manulife', $payroll_year . '-' . $payroll_month . '---lfa-manulife-type-2.xlsx', 'life', 2),
              PayrollHelper::generate_feed('lfa', 'navigator', $payroll_year . '-' . $payroll_month . '---lfa-navigator.xlsx', 'cis'),
              PayrollHelper::generate_feed('lfa', 'ntuc', $payroll_year . '-' . $payroll_month . '---lfa-ntuc-esis.xlsx', 'health'),
              PayrollHelper::generate_feed('lfa', 'ntuc', $payroll_year . '-' . $payroll_month . '---lfa-ntuc-life.xlsx', 'life'),
              PayrollHelper::generate_feed('lfa', 'rhi', $payroll_year . '-' . $payroll_month . '---lfa-raffles-health.xlsx', 'health'),
              PayrollHelper::generate_feed('lfa', 'tm', $payroll_year . '-' . $payroll_month . '---lfa-tm-go.xlsx', 'group-override'),
              PayrollHelper::generate_feed('lfa', 'tm', $payroll_year . '-' . $payroll_month . '---lfa-tm-life.xlsx', 'life'),
            ]
        ];

        // PayrollBatchProcessor::dispatch($batch_summary)->onQueue('Payroll-Batches');


        // Create New Batch File
        $batch_record = PayrollBatch::firstOrCreate([
            'year' => $batch_summary['batch']['year'],
            'month' => $batch_summary['batch']['month']
        ]);

        // Remove prior records belonging to Feed Record ...
        // $batch_record->feeds()->delete();

        // Updating the updated_at timestamp ...
        $batch_record->touch();

        // Begin processing each feed
        foreach($batch_summary['feeds'] as $feed) {
            // Find out payroll type ...
            $payroll_types =
                (in_array($feed['era'], ['gi', 'adjustments'])) ?
                    SelectPayrollFeedType::continuePayrollEra($feed['era']) :
                    SelectPayrollFeedType::continuePayrollEra($feed['era'])->continueProviderAlias($feed['provider'])->continuePayrollCategory($feed['category']);
            $feed_type = $payroll_types->type($feed['type'] ?? null)->first();
            $feed_provider = SelectProvider::firstAlias($feed['provider']);

            // Remove prior feed records belonging to current Payroll Batch ...
            $batch_record->feeds()->where('year', $batch_record->year)->where('month', $batch_record->month)->where('filename', $feed['filename'])->delete();

            // Create New Feed Record (if doesnt exists) ...
            $feed_record = PayrollFeed::updateOrCreate([
                'batch_id' => $batch_record->id,
                'year' => $batch_record->year,
                'month' => $batch_record->month,
                'filename' => $feed['filename'],
                'payroll_type_slug' => $feed_type['slug'],
                'provider_slug' => $feed_provider->slug,
                'gst_rate' => $feed['gst_rate'] ?? 0.00,
                'csv_pipe' => $feed['csv_pipe'] ?? false
            ], [
                'date_received' => $feed['date_received'] ?? null,
                'processed' => false
            ]);

            // Updating the updated_at timestamp ...
            $feed_record->touch();
        }

        // Re-validate all feeds that belongs to this batch
        foreach($batch_record->feeds as $feed) {
            $this->command->info('Processing ' . $feed['filename']);
            // Dispatch job for verifying feed
            // PayrollFeedProcessor::dispatch($feed)->onQueue('Payroll-Feeds');

            // Remove prior records belonging to Feed Record ...
            $feed->records()->delete();

            // Update Feed Record status...
            $feed->update(['processed' => false]);

            // Get storage location
            $path_prefix = 'payroll/uploads/' . $feed->batch->year . '/' . $feed->batch->month . '/';

            // Set default values for each feed...
            $feed_type = $feed->feed_type;
            $collection = null;
            $fast_excel = false;
            // $feed_chunking = $feed->chunk;
            $feed_chunking = false;

            // Retrieve mapping record for payroll type ...
            $map = $feed_type->mapping->toArray();

            // Try to import via Laravel-Excel first, if 'UnreadableFileException', lets try FastExcel..
            try {
                // If feed file is a CSV...
                if ($feed['csv_pipe']) {
                    $collection = (new PayrollFeedsImportCsvPipe)->toCollection($path_prefix.$feed['filename'])->flatten(1);
                } else {
                    $collection = (new PayrollFeedsImport)->toCollection($path_prefix.$feed['filename'])->flatten(1);
                }
                $this->command->info('Collection using Laravel-Excel');
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                // If feed file is a CSV...
                if (strtolower(substr($feed['filename'], -3)) == "csv") {
                    $collection = (new FastExcel)->configureCsv($delimiter = ($feed['csv_pipe'])?'|':',', '')->import(storage_path('app/' . $path_prefix . $feed['filename']));
                } else {
                    $collection = (new FastExcel)->import(storage_path('app/' . $path_prefix . $feed['filename']));
                }
                $fast_excel = true;
                $this->command->info('Collection using Fast-Excel');
            }


            try {
                // Preview the total commissions from import file
                if ($fast_excel){
                    foreach($collection->first() as $key => $value) {
                        if ($map['commission'] == str_slug($key, '_')) $fastexcel_comm_key = $key;
                    }
                }
                $sum_preview = ($fast_excel) ? $collection->sum($fastexcel_comm_key) : $collection->sum($map['commission']);
                $feed->update(['import_commission' => $sum_preview]);
            } catch (Exception $e) {
                \Log::info($e->getMessage());
            }

            // // TODO :: Check for row headers in CSV file
            // $headings = (new HeadingRowImport)->toArray($path_prefix.$feed['filename']);
            // // TODO :: Allow personnel to verify row headers mappings
            // // TODO :: Allow personnel to update headers accordingly for future imports (new mapping)

            // // Process the feed as it is.. (without chunking)
            // $feed->process_feed([
            //     'fast_excel' => $fast_excel,
            //     'collection' => $collection
            // ]);
            PayrollHelper::process_feed($feed, [
                'fast_excel' => $fast_excel,
                'collection' => $collection
            ]);
        }
    }
}
