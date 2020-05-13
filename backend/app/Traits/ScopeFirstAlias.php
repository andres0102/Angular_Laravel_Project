<?php

namespace App\Traits;

trait ScopeFirstAlias
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstAlias($query, $value) {
      $results = $query->where('alias', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
