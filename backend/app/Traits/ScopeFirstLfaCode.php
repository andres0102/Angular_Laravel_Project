<?php

namespace App\Traits;

trait ScopeFirstLfaCode
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstLfaCode($query, $value) {
      $results = $query->where('lfa_code', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
