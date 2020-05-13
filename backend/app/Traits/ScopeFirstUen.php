<?php

namespace App\Traits;

trait ScopeFirstUen
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstUen($query, $value) {
      $results = $query->where('reference_uen', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
