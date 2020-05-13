<?php

namespace App\Models\LegacyFA\Clients;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Models\LegacyFA\Clients\{BaseModel, Client, IntroducerGift, Nominee};
use App\Models\LegacyFA\Submissions\IntroducerCase;
use App\Models\LegacyFA\Associates\Associate;
use App\Traits\{ScopeFirstUuid, ScopeFirstPid, HasNominee};

class Introducer extends BaseModel
{
    use SoftDeletes,
        HasNominee,
        ScopeFirstPid,
        ScopeFirstUuid;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'introducers';

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
        'date_start' => 'datetime:Y-m-d',
        'date_end' => 'datetime:Y-m-d',
    ];
    public function setDateStartAttribute($date) { $this->attributes['date_start'] = ($date) ? Carbon::parse($date) : null; }
    public function setDateEndAttribute($date) { $this->attributes['date_end'] = ($date) ? Carbon::parse($date) : null; }

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
        self::created(function ($model) {
            $client = $model->client;
            $client->log(auth()->user(), 'introducer_created', 'Introducer record created.', null, $model, 'introducers', $model->uuid);
        });
    }

    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function sales_associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function client() { return $this->belongsTo(Client::class, 'client_uuid', 'uuid'); }
    public function gifts() { return $this->hasMany(IntroducerGift::class, 'introducer_uuid', 'uuid'); }
    public function introducer_case() { return $this->hasMany(IntroducerCase::class, 'introducer_uuid', 'uuid'); }
    public function getNameAttribute() { return $this->client->name; }
}
