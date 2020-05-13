<?php

namespace App\Models\LegacyFA\Payroll;
use App\Models\LegacyFA\Payroll\{BaseModel, PayrollFeed, PayrollRecord};

class PayrollBatch extends BaseModel
{
    protected $withCount = ['feeds'];

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'batches';

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function feeds() { return $this->hasMany(PayrollFeed::class, 'batch_id'); }
    public function records() { return $this->hasManyThrough(PayrollRecord::class, PayrollFeed::class, 'batch_id', 'feed_id', 'id', 'id'); }


    /** ===================================================================================================
     * Return custom attributes.
     *
     * @param  string  $value
     * @return string
     */
    public function getDepositsAttribute() { return $this->feeds->sum('total_feed_deposits'); }
    public function getCommissionsAttribute() { return $this->feeds->sum('commissions'); }


    public function getFeedsCheckAttribute() {
        $arr_unmatch = [];
        $feeds = $this->feeds;

        foreach($feeds as $fd) {
            $feed_records = $fd->records;
            $record_check_array = [];

            foreach($feed_records as $rec) {
                $record_commission = (float)$rec->commission * (float)$rec->commission_conversion_rate;
                $record_computations = (float)$rec->computations()->sum('amount');
                if (abs($record_commission - $record_computations) > 0.01) array_push($record_check_array, $rec->id);
            }

            if (empty($record_check_array)) {
                array_push($arr_unmatch, [
                    'feed_filename' => $fd->filename,
                    'import_commission' => $fd->import_commission,
                    'records' => $fd->records_total->first()['computed_amount'],
                    'records_gst' => $fd->records()->sum('commission_gst'),
                    'computations' => $fd->computations_total,
                    'status' => 'OK'
                ]);
            } else {
                array_push($arr_unmatch, [
                    'feed_filename' => $fd->filename,
                    'import_commission' => $fd->import_commission,
                    'records' => $fd->records_total->first()['computed_amount'],
                    'records_gst' => $fd->records()->sum('commission_gst'),
                    'computations' => $fd->computations_total,
                    'status' => count($record_check_array) . ' records with more than 0.01 difference in computations'
                ]);
            }
        }

        return empty($arr_unmatch) ? null : $arr_unmatch;
    }
}
