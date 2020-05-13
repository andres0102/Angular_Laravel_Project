<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Users\User;
use App\Models\LegacyFA\Associates\Associate;

trait HasAssociateAccess
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function associate_access() { return $this->belongsToMany(Associate::class, 'lfa_associates.associates_has_users', 'user_uuid', 'associate_uuid', 'uuid', 'uuid')->withPivot('default')->withTimestamps(); }

    public function default_associate() { return $this->associate_access()->wherePivot('default', true); }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getDefaultAssociateAttribute() { return $this->default_associate()->first(); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function addAssociateAccess(Associate $associate, $default = false)
    {
        // Make sure there is only one default user...
        if ($default === true) {
            // There is only one default, so lets remove all before updating or
            $this->associate_access()->update(['default' => false]);
            $this->is_rep = true;
        }
        if (!$this->associate_access->contains($associate)) return $this->associate_access()->save($associate, ['default' => true]);
        else return $this->updateAssociateAccess($associate, $default);
    }

    public function removeAssociateAccess(Associate $associate)
    {
        $this->associate_access()->detach($associate->uuid);
        if (!$this->default_associate) $this->is_rep = false;
    }

    public function removeAllAssociateAccess()
    {
        $this->associate_access()->detach();
        if (!$this->default_associate) $this->is_rep = false;
    }

    public function hasAssociateAccess(Associate $associate)
    {
        return $this->hasRole('super-admin') ? true : $this->associate_access->contains($associate);
    }

    public function updateAssociateAccess(Associate $associate, $default)
    {
        return $this->associate_access()->updateExistingPivot($associate->uuid, ['default' => $default]);
    }
}
