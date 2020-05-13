<?php

namespace App\Models\LegacyFA\Associates;
use Carbon\Carbon;
use App\Models\LegacyFA\Associates\{BaseModel, Associate};
use Illuminate\Support\Str;

class BandingGI extends BaseModel
{
    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bandings_gi';

    /** ===================================================================================================
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_start' => 'datetime:Y-m-d',
        'date_end' => 'datetime:Y-m-d',
        'rank' => 'integer'
    ];
    public function setDateStartAttribute($date) { $this->attributes['date_start'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateEndAttribute($date) { $this->attributes['date_end'] = ($date) ? Carbon::parse($date) : null; }

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }

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
            $associate->log(auth()->user(), 'banding_gi_created', 'GI Banding record created.', null, $model, 'banding_gi', $model->uuid);
        });
    }
}
