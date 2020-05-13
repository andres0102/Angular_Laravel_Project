<?php

namespace App\Models\LegacyFA\Payroll;
use App\Models\LegacyFA\Payroll\{BaseModel, PayrollRecord, PayrollComputation, PayrollAdjustment};
use App\Traits\{TraitPayroll}; // ProcessFeed

class PayrollFeed extends BaseModel
{
    use TraitPayroll; // ProcessFeed

    protected $withCount = [
        'records',
        'records_processed',
        'records_unprocessed',
        'records_valid',
        'records_invalid',
        'records_verified',
        'records_unverified'
    ];

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'feeds';

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_received' => 'date'
    ];

    /** ===================================================================================================
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'records_total',
        'computations_total'
    ];

    /** ===================================================================================================
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['batch'];

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function adjustments() { return $this->hasMany(PayrollAdjustment::class, 'feed_id'); }
    public function records() { return $this->hasMany(PayrollRecord::class, 'feed_id'); }
    public function computations() { return $this->hasMany(PayrollComputation::class, 'feed_id'); }
    // Records Processed ??
    public function records_processed() { return $this->hasMany(PayrollRecord::class, 'feed_id')->where('processed', true); }
    public function records_unprocessed() { return $this->hasMany(PayrollRecord::class, 'feed_id')->where('processed', false); }
    // Records Valid ??
    public function records_valid() { return $this->hasMany(PayrollRecord::class, 'feed_id')->where('validated', true); }
    public function records_invalid() { return $this->hasMany(PayrollRecord::class, 'feed_id')->where('validated', false); }
    // Records Verified ??
    public function records_verified() { return $this->hasMany(PayrollRecord::class, 'feed_id')->where('verified_agent_code', true); }
    public function records_unverified() { return $this->hasMany(PayrollRecord::class, 'feed_id')->where('verified_agent_code', false); }
    // public function computations() { return $this->hasManyThrough(PayrollComputation::class, PayrollRecord::class, 'feed_id', 'record_id', 'id', 'id'); }


    /** ===================================================================================================
     * Return custom attributes.
     *
     * @param  string  $value
     * @return string
     */
    public function getRecordsTotalAttribute() { return $this->records()->select(\DB::raw('sum(commission * commission_conversion_rate) as computed_amount'))->get(); }
    public function getComputationsTotalAttribute() { return $this->computations()->sum('amount'); }

}
