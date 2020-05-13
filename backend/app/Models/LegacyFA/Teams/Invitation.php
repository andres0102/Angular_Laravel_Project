<?php

namespace App\Models\LegacyFA\Teams;
use App\Models\LegacyFA\Teams\{BaseModel, Team};

class Invitation extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'memberships';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'team_uuid'
    ];

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function team() { return $this->belongsTo(Team::class, 'team_uuid', 'uuid'); }
}
