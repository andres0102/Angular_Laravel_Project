<?php

namespace App\Traits;
use App\Models\Selections\LegacyFA\SelectProvider;

trait ScopeContinueProviderAlias
{
    /** ===================================================================================================
     * Scope a query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function ScopeContinueProviderAlias($query, $value) {
      $provider = SelectProvider::firstAlias($value);
      return $query->where('provider_slug', $provider->slug ?? null); // ->get()
    }
}
