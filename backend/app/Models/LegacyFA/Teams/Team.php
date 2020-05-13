<?php

namespace App\Models\LegacyFA\Teams;
use App\Models\LegacyFA\Teams\{BaseModel, Membership};
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\{ScopeFirstCode, ScopeFirstUuid};
use Illuminate\Support\Str;

use App\Models\Selections\LegacyFA\SelectTeamType;
use App\Models\LegacyFA\Associates\Associate;

class Team extends BaseModel
{
    use SoftDeletes, ScopeFirstCode, ScopeFirstUuid;

    protected $with = 'type';
    protected $withCount = 'membership';

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'teams';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'uuid'
    ];

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /** ===================================================================================================
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /** ===================================================================================================
     *  Setup model event hooks
     *
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = Str::orderedUuid()->toString();
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function type() { return $this->belongsTo(SelectTeamType::class, 'type_slug', 'slug'); }
    public function owner() { return $this->belongsTo(Associate::class, 'owner_uuid', 'uuid'); }
    public function membership() { return $this->hasMany(Membership::class, 'team_uuid', 'uuid'); }
    public function members() { return $this->belongsToMany(Associate::class, 'lfa_teams.memberships', 'team_uuid', 'associate_uuid', 'uuid', 'uuid')->withPivot('role_slug'); }
}
