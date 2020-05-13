<?php

namespace App\Models\LegacyFA\Associates;

use Carbon\Carbon;
use App\Models\LegacyFA\Associates\{BaseModel, Associate};
use App\Models\Selections\LegacyFA\SelectDesignation;
use App\Traits\{ScopeFirstAaCode, ScopeFirstLfaCode};
use Illuminate\Support\Str;

class Movement extends BaseModel
{
    use ScopeFirstAaCode, ScopeFirstLfaCode;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'movements';

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_start' => 'datetime:Y-m-d',
        'date_end' => 'datetime:Y-m-d',
    ];
    public function setDateStartAttribute($date) { $this->attributes['date_start'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateEndAttribute($date) { $this->attributes['date_end'] = ($date) ? Carbon::parse($date) : null; }

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function reporting_to() { return $this->belongsTo(Associate::class, 'reporting_uuid', 'uuid'); }
    public function designation() { return $this->belongsTo(SelectDesignation::class,'designation_slug', 'slug'); }

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
        self::created(function ($model) {
            $associate = $model->associate;
            $associate->log(auth()->user(), 'movement_created', 'Movement record created.', null, $model, 'movements', $model->uuid);
        });
        self::saved(function ($model) {
            // Update Teams Record
            $model->associate->update_teams();
            $model->associate->user->update_designation();
        });
    }
}
