<?php

namespace App\Traits;

trait ScopeFirstUuid
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstUuid($query, $value) {
      $results = $query->where('uuid', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
