<?php

namespace App\Traits;

trait ScopeFirstStep
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstStep($query, $value) {
      $results = $query->where('step', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
