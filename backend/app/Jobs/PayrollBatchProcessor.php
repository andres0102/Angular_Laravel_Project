<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels, InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Jobs\PayrollFeedProcessor;
use App\Models\LegacyFA\Payroll\{PayrollBatch, PayrollFeed};

use App\Models\Selections\LegacyFA\{SelectProvider, SelectPayrollFeedType};

class PayrollBatchProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $batch_summary;

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
    public function __construct($batch_summary)
    {
        $this->batch_summary = $batch_summary;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Create New Batch File
        $batch_record = PayrollBatch::firstOrCreate([
            'year' => $this->batch_summary['batch']['year'],
            'month' => $this->batch_summary['batch']['month']
        ]);

        // Remove prior records belonging to Feed Record ...
        // $batch_record->feeds()->delete();

        // Updating the updated_at timestamp ...
        $batch_record->touch();

        // Begin processing each feed
        foreach($this->batch_summary['feeds'] as $feed) {
            // Find out payroll type ...
            $payroll_types =
                (in_array($feed['era'], ['gi', 'adjustments'])) ?
                    SelectPayrollFeedType::continuePayrollEra($feed['era']) :
                    SelectPayrollFeedType::continuePayrollEra($feed['era'])->continueProviderAlias($feed['provider'])->continuePayrollCategory($feed['category']);
            $feed_type = $payroll_types->type($feed['type'] ?? null)->first();
            $feed_provider = ($feed['era'] !== 'adjustments') ? SelectProvider::firstAlias($feed['provider']) : null;

            // Remove prior feed records belonging to current Payroll Batch ...
            $batch_record->feeds()->where('year', $batch_record->year)->where('month', $batch_record->month)->where('filename', $feed['filename'])->delete();

            // Create New Feed Record (if doesnt exists) ...
            $feed_record = PayrollFeed::updateOrCreate([
                'batch_id' => $batch_record->id,
                'year' => $batch_record->year,
                'month' => $batch_record->month,
                'filename' => $feed['filename'],
                'payroll_type_slug' => $feed_type['slug'],
                'provider_slug' => ($feed_provider) ? $feed_provider->slug : null,
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
            // Dispatch job for verifying feed
            PayrollFeedProcessor::dispatch($feed)->onQueue('Payroll-Feeds');
        }
    }

    /**
    * Get the tags that should be assigned to the job.
    *
    * @return array
    */
    public function tags()
    {
        return ['Payroll Batch ' . $this->batch_summary['batch']['year'] . '-' . $this->batch_summary['batch']['month']];
    }
}
