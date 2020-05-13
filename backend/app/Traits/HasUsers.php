<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Users\User;
use App\Models\LegacyFA\Associates\Associate;

trait HasUsers
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function users() { return $this->belongsToMany(User::class, 'lfa_associates.associates_has_users', 'associate_uuid', 'user_uuid', 'uuid', 'uuid')->withPivot('default')->withTimestamps(); }
    public function default_user() { return $this->belongsToMany(User::class, 'lfa_associates.associates_has_users', 'associate_uuid', 'user_uuid', 'uuid', 'uuid')->wherePivot('default', true)->withTimestamps(); }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getDefaultUserAttribute() { return $this->default_user()->first(); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function add_user_access(User $user, $default = false) {
        // Make sure there is only one default user...
        if ($default === true) $this->default_user()->detach();
        if (!$this->has_user($user)) return $this->users()->save($user, ['default' => $default]);
        else return $this->update_user($user, $default);
    }
    public function remove_user_access(User $user) {
        return $this->users()->detach($user->uuid);
    }
    public function has_user(User $user) { return $this->users->contains($user); }
    public function update_user(User $user, $default) { return $this->users()->updateExistingPivot($user->uuid, ['default' => $default]); }
}
