<?php

namespace App\Traits;

trait ScopeFirstSlug
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstSlug($query, $value) {
      $results = $query->where('slug', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
