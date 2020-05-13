<?php

namespace App\Models\LegacyFA\Teams;
use App\Models\LegacyFA\Teams\{BaseModel, Team};
use App\Models\Selections\LegacyFA\{TeamsRole};
use App\Models\LegacyFA\Associates\{Associate};

class Membership extends BaseModel
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
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function role() { return $this->belongsTo(TeamsRole::class, 'role_slug', 'slug'); }
}
