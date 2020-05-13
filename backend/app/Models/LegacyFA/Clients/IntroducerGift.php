<?php

namespace App\Models\LegacyFA\Clients;
use App\Models\LegacyFA\Clients\{BaseModel, Introducer};

class IntroducerGift extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'introducers_gifts';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function introducer() { return $this->belongsTo(Introducer::class, 'introducer_uuid', 'uuid'); }
}
