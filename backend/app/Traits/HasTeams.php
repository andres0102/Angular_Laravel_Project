<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\LegacyFA\Teams\Team;
use App\Models\Selections\LegacyFA\{SelectTeamType};

trait HasTeams
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function teams() { return $this->belongsToMany(Team::class, 'lfa_teams.memberships', 'associate_uuid', 'team_uuid', 'uuid', 'uuid')->withPivot('role_slug'); }
    public function sales_group() { return $this->teams()->where('type_slug', 'group'); }
    public function sales_unit() { return $this->teams()->where('type_slug', 'unit'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function active_sales_agents($date = null)
    {
        if (!$this->is_manager) return null;

        $carbon_date = $date ?? Carbon::now();
        $group = $this->sales_group()->first();
        $unit = $this->sales_unit()->first();
        if (!isset($group, $unit)) return null;

        if ($group->owner->is($this)) {
            return $group->members->filter(function ($value, $key) use ($carbon_date) {
                return $value->first_day->isBefore($carbon_date) && (!$value->last_day || $value->last_day->isAfter($carbon_date));
            });
        } else if ($unit->owner->is($this)) {
            return $unit->members->filter(function ($value, $key) use ($carbon_date) {
                return $value->first_day->isBefore($carbon_date) && (!$value->last_day || $value->last_day->isAfter($carbon_date));
            });
        } else {
            return null;
        }
    }

    public function update_teams()
    {
        // Remove all teams from the associate
        $this->teams()->detach();
        if ($lfa_code = $this->lfa_code) {
            // Add associate to respective Director Groups
            $p_groupcode = substr($lfa_code, 2, 2);
            $group = Team::where('type_slug', 'group')->whereCode($p_groupcode)->first();
            $group->membership()->create([
                'associate_uuid' => $this->uuid,
                'role_slug' => 'member'
            ]);

            // Add associate to respective Managerial Units
            $p_unitcode = substr($lfa_code, 0, 2);
            $unit = Team::where('type_slug', 'unit')->whereCode($p_unitcode)->first();
            $unit->membership()->create([
                'associate_uuid' => $this->uuid,
                'role_slug' => 'member'
            ]);
        }
    }

}
