<?php

namespace App\Models\LegacyFA\Products;
use Carbon\Carbon;
use App\Models\LegacyFA\Products\{BaseModel, ProductOption};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia\{HasMedia, HasMediaTrait};
use App\Traits\{ScopeFirstUen, ScopeFirstUuid, TraitProducts};
use App\Models\Selections\LegacyFA\{SelectProvider};

class Product extends BaseModel implements HasMedia
{
    use SoftDeletes,
        HasMediaTrait,
        TraitProducts,
        ScopeFirstUen, ScopeFirstUuid;

    /** ===================================================================================================
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';

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

            // Update Slug
            if ($model->reference_uen) {
                // Product UEN is defined
                $model->slug = Str::slug($model->reference_uen);
            } else {
                // Product UEN is not defined, manually set slug
                $provider = SelectProvider::firstSlug($model->provider)->first();
                $model->slug = Str::slug($provider->alias . '-' . Str::slug(snake_case($model->name)));
            }
        });

        self::updating(function ($model) {
            // Update Slug
            if ($model->reference_uen) {
                // Product UEN is defined
                $model->slug = Str::slug($model->reference_uen);
            } else {
                // Product UEN is not defined, manually set slug
                $provider = SelectProvider::firstSlug($model->provider)->first();
                $model->slug = Str::slug($provider->alias . '-' . Str::slug(snake_case($model->name)));
            }
        });
    }


    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function provider() { return $this->belongsTo(SelectProvider::class, 'provider_slug', 'slug'); }
    public function options() { return $this->hasMany(ProductOption::class, 'product_uuid', 'uuid'); }


    /** ===================================================================================================
     * Media Collections
     *
     */
    public function getImageAttribute() { return $this->getFirstMediaUrl('product-images'); }
    public function addImage($pathToFile)
    {
        return $this->addMedia($pathToFile)
                    ->setName($this->name)
                    ->setFileName($this->uuid . '.' . pathinfo($pathToFile, PATHINFO_EXTENSION))
                    ->toMediaCollection('product-images');
    }
    public function registerMediaCollections()
    {
        $this->addMediaCollection('product-images')
             ->useDisk('products')
             ->singleFile();
    }
}
