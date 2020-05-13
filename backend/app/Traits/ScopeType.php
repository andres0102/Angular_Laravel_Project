<?php

namespace App\Traits;

trait ScopeType
{
    /** ===================================================================================================
     * Scope a query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeType($query, $value) {
      return $query->where('type', $value)->get();
    }
}
