<?php

namespace App\Models\LegacyFA\Payroll;
use App\Models\LegacyFA\Payroll\BaseModel;
use App\Traits\TraitPayroll;
use App\Models\LegacyFA\Associates\{Associate};
use App\Models\LegacyFA\Clients\{Client, ClientPolicy, ClientPolicyTransaction};

class PayrollComputation extends BaseModel
{
    use TraitPayroll;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'computations';

    /** ===================================================================================================
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['record'];

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function payee() { return $this->belongsTo(Associate::class, 'payee_uuid', 'uuid'); }
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function closed_by() { return $this->belongsTo(Associate::class, 'closed_by_uuid', 'uuid'); }
    public function client() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function policy() { return $this->belongsTo(ClientPolicy::class, 'policy_uuid', 'uuid'); }
    public function transaction() { return $this->belongsTo(ClientPolicyTransaction::class, 'policy_transaction_uuid', 'uuid'); }
}
