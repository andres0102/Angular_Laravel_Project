<?php

namespace App\Models\LegacyFA\Products;
use App\Models\LegacyFA\Products\{BaseModel, Product, Rider};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Traits\{ScopeFirstPid, ScopeFirstUen, ScopeFirstUuid, ScopeActivated};
use App\Models\Selections\LegacyFA\{SelectProductCategory};

class ProductOption extends BaseModel
{
    use SoftDeletes, ScopeFirstPid, ScopeFirstUen, ScopeFirstUuid, ScopeActivated;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_options';

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
    public function category() { return $this->belongsTo(SelectProductCategory::class, 'product_cat_slug', 'slug'); }
    public function product() { return $this->belongsTo(Product::class, 'product_uuid', 'uuid'); }
    public function riders() { return $this->belongsToMany(Rider::class, 'lfa_products.product_options_has_riders', 'product_option_uuid', 'rider_uuid', 'uuid', 'uuid'); }
}
