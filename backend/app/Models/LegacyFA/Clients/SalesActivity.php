<?php

namespace App\Models\LegacyFA\Clients;

use Illuminate\Support\Str;
use App\Models\LegacyFA\Clients\{BaseModel, Client};
use App\Models\LegacyFA\Associates\Associate;

class SalesActivity extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sales_activities';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function client() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function sales_associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
}
