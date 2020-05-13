<?php

namespace App\Traits;

trait ScopeFirstCode
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstCode($query, $value) {
      $results = $query->where('code', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
