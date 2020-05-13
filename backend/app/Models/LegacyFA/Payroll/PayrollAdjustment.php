<?php

namespace App\Models\LegacyFA\Payroll;
use App\Models\LegacyFA\Payroll\{BaseModel, PayrollComputation};
use App\Models\LegacyFA\Associates\Associate;
use App\Helpers\PayrollHelper;
use App\Traits\TraitPayroll;

class PayrollAdjustment extends BaseModel
{
    use TraitPayroll; // ProcessCommission

    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_payroll';

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'adjustments';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /** ===================================================================================================
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_transaction' => 'date',
    ];

    /** ===================================================================================================
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /** ===================================================================================================
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['feed'];

    /** ===================================================================================================
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /** ===================================================================================================
     *  Setup model event hooks
     *
     */
    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            PayrollHelper::process_adjustments($model);
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function computations() { return $this->hasMany(PayrollComputation::class, 'record_id')->where('record_type', 'adjustments'); }
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }

}
