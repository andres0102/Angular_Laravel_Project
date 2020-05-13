<?php

namespace App\Traits;

trait ScopeFirstSn
{
    /** ===================================================================================================
     * Scope a query, return the first row with specific value or false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeFirstSn($query, $value) {
      $results = $query->where('lfa_sl_no', $value)->get();
      return ($results->count() > 0) ? $results->first() : false;
    }
}
