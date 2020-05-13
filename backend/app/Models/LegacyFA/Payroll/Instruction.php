<?php

namespace App\Models\LegacyFA\Payroll;
use App\Models\LegacyFA\Payroll\BaseModel;
use App\Models\LegacyFA\Associates\Associate;

class Instruction extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'instructions';

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_effective' => 'datetime',
    ];

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function from_associate() { return $this->belongsTo(Associate::class, 'from_associate_uuid', 'uuid'); }
    public function to_associate() { return $this->belongsTo(Associate::class, 'to_associate_uuid', 'uuid'); }
}
