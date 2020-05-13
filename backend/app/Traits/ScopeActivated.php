<?php

namespace App\Traits;

trait ScopeActivated
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeActivated($query) {
      return $query->where('activated', true);
    }
}
