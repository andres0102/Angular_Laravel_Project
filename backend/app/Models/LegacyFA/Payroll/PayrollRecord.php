<?php

namespace App\Models\LegacyFA\Payroll;
use App\Models\LegacyFA\Payroll\{BaseModel, PayrollComputation};
use App\Models\LegacyFA\Clients\{Client, ClientPolicy, ClientPolicyTransaction};
use App\Models\LegacyFA\Associates\Associate;
use App\Helpers\PayrollHelper;
use App\Traits\TraitPayroll;

class PayrollRecord extends BaseModel
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
    protected $table = 'records';

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
        'date_issued' => 'date',
        'date_inception' => 'date',
        'date_expiry' => 'date',
        'date_transaction' => 'date',
        'date_instalment_from' => 'date',
        'date_instalment_to' => 'date',
        'date_due' => 'date',
        'date_commission' => 'date'
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
            if ($model->policy_transaction_uuid) ClientPolicyTransaction::whereUuid($model->policy_transaction_uuid)->first()->update(['payroll_record_id' => $model->id]);
            if ($model->validated) {
                $model->client->update([
                    'is_lead' => false,
                    'lead_stage_slug' => 'converted-to-client',
                    'sales_stage_slug' => 'incepted-policy',
                ]);
                PayrollHelper::process_commissions($model);
            }
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function computations() { return $this->hasMany(PayrollComputation::class, 'record_id')->where('record_type', 'payroll_records'); }
    // public function computations() { return $this->hasMany(PayrollComputation::class, 'record_id'); }

    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function client() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function policy() { return $this->belongsTo(ClientPolicy::class, 'policy_uuid', 'uuid'); }
    public function transaction() { return $this->belongsTo(ClientPolicyTransaction::class, 'policy_transaction_uuid', 'uuid'); }

}
