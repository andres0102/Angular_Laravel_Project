<?php

namespace App\Traits;

trait ScopeContinuePayrollEra
{
    /** ===================================================================================================
     * Scope a query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeContinuePayrollEra($query, $value) {
      return $query->where('era', $value); // ->get()
    }
}
