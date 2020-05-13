<?php

namespace App\Traits;

trait ScopeContinuePayrollCategory
{
    /** ===================================================================================================
     * Scope a query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeContinuePayrollCategory($query, $value) {
      return $query->where('payroll_cat_slug', $value); // ->get()
    }
}
