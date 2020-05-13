<?php

namespace App\Models\LegacyFA\Products;
use App\Models\LegacyFA\Products\{BaseModel, ProductOption};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\{ScopeFirstPid, ScopeFirstUen, ScopeFirstUuid, TraitProducts};

class Rider extends BaseModel
{
    use SoftDeletes, ScopeFirstPid, ScopeFirstUen, ScopeFirstUuid, TraitProducts;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'riders';

    /** ===================================================================================================
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'uuid'
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
    public function product_options() { return $this->belongsToMany(ProductOption::class, 'lfa_products.product_options_has_riders', 'rider_uuid', 'product_option_uuid', 'uuid', 'uuid'); }
}
