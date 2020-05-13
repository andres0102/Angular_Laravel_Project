<?php

namespace App\Models\LegacyFA\Associates;
use Illuminate\Support\Str;
use App\Models\LegacyFA\Associates\{BaseModel, Associate};
use App\Models\Selections\LegacyFA\SelectProvider;
use App\Traits\ScopeContinueProviderAlias;

class ProviderCode extends BaseModel
{
    use ScopeContinueProviderAlias;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'providers_codes';

    /** ===================================================================================================
     * Eloquent Model Relationships
     * @var array
     */
    public function associate() { return $this->belongsTo(Associate::class, 'associate_uuid', 'uuid'); }
    public function provider() { return $this->belongsTo(SelectProvider::class, 'provider_slug', 'slug'); }

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
            $associate->log(auth()->user(), 'provider_code_tagged', 'Provider Code tagged.', null, $model, 'providers_codes', $model->uuid);
        });
    }
}
