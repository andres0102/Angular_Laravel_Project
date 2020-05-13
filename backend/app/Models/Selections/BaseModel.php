<?php

namespace App\Models\Selections;
use App\Models\DefaultModel;
use Spatie\Sluggable\{HasSlug, SlugOptions};
use App\Traits\{AttributeName, ScopeActivated, ScopeFirstSlug};

class BaseModel extends DefaultModel
{
    use HasSlug;
    use AttributeName;
    use ScopeActivated;
    use ScopeFirstSlug;

    /** ===================================================================================================
    * The connection name for the model.
    *
    * @var string
    */
    protected $connection = 'lfa_selections';

    /** ===================================================================================================
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /** ===================================================================================================
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /** ===================================================================================================
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        if ($this->title) {
            return SlugOptions::create()
                ->generateSlugsFrom('title')
                ->saveSlugsTo('slug');
        } else if ($this->full_name) {
            return SlugOptions::create()
                ->generateSlugsFrom('full_name')
                ->saveSlugsTo('slug');
        }
    }
}
