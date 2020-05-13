<?php

namespace App\Traits;

trait ScopeFirstPid
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstPid($query, $value) {
      $results = $query->where('reference_pid', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
