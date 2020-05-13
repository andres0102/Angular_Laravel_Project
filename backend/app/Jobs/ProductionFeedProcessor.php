<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\{SerializesModels, InteractsWithQueue};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Rap2hpoutre\FastExcel\FastExcel;
use Maatwebsite\Excel\Exceptions\UnreadableFileException;

use App\Jobs\PayrollRecordsChunkProcessor;
use App\Imports\{PayrollFeedsImport, PayrollFeedsImportCsvPipe};

class ProductionFeedProcessor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $feed;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($feed)
    {
        $this->feed = $feed;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Remove prior records belonging to Feed Record ...
        $this->feed->records()->delete();

        // Update Feed Record status...
        $this->feed->update(['processed' => false]);

        // Get storage location
        $path_prefix = 'payroll/uploads/' . $this->feed->batch->year . '/' . $this->feed->batch->month . '/';

        // Set default values for each feed...
        $feed_type = $this->feed->feed_type;
        $collection = null;
        $fast_excel = false;
        // $feed_chunking = $this->feed->chunk;
        $feed_chunking = false;

        // Retrieve mapping record for payroll type ...
        $map = $feed_type->mapping->toArray();

        // Try to import via Laravel-Excel first, if 'UnreadableFileException', lets try FastExcel..
        try {
            // If feed file is a CSV...
            if ($this->feed['csv_pipe']) {
                $collection = (new PayrollFeedsImportCsvPipe)->toCollection($path_prefix.$this->feed['filename'])->flatten(1);
            } else {
                $collection = (new PayrollFeedsImport)->toCollection($path_prefix.$this->feed['filename'])->flatten(1);
            }
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            // If feed file is a CSV...
            if (strtolower(substr($this->feed['filename'], -3)) == "csv") {
                $collection = (new FastExcel)->configureCsv($delimiter = ($this->feed['csv_pipe'])?'|':',', '')->import(storage_path('app/' . $path_prefix . $this->feed['filename']));
            } else {
                $collection = (new FastExcel)->import(storage_path('app/' . $path_prefix . $this->feed['filename']));
            }
            $fast_excel = true;
        }


        // Preview the total commissions from import file
        if ($fast_excel){
            foreach($collection->first() as $key => $value) {
                if ($map['commission'] == str_slug($key, '_')) $fastexcel_comm_key = $key;
            }
        }
        $sum_preview = ($fast_excel) ? $collection->sum($fastexcel_comm_key) : $collection->sum($map['commission']);
        $this->feed->update(['import_commission' => $sum_preview]);



        // Different ways to process the collections
        if ($feed_chunking) {
            // Split feed into smaller chunks
            $records_chunk = $collection->chunk(1000);
            $chunk_count = 0;
            foreach($records_chunk as $chunk) {
                $chunk_count++;
                $feed_chunk = [
                    'feed' => $this->feed,
                    'collection' => $chunk,
                    'fast_excel' => $fast_excel,
                    'count' => $chunk_count,
                    'total' => $records_chunk->count(),
                ];
                // Dispatch job for verifying feed
                PayrollRecordsChunkProcessor::dispatch($feed_chunk)->onQueue('Payroll-Records');
            }

        } else {
            // Process the feed as it is.. (without chunking)
            $this->feed->process_feed([
                'fast_excel' => $fast_excel,
                'collection' => $collection
            ]);
        }
    }

    /**
    * Get the tags that should be assigned to the job.
    *
    * @return array
    */
    public function tags()
    {
        return ['Payroll-Feed', 'Feed: '. strtoupper('(' . $this->feed->provider_name . ') ' . $this->feed->feed_type->title)];
    }
}
