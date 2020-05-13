<?php

namespace App\Traits;

use App\Helpers\Common;
use Carbon\Carbon;
use App\Models\LegacyFA\Associates\Movement;

trait HasMovements
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function movements() { return $this->hasMany(Movement::class, 'associate_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */

    public function query_movement($check_date = null, $payroll_era = null)
    {
        // Default return values
        if ($latest_movement = $this->latest_movement) {
            // Associate has at least one movement record in database..
            // Lets check if values provided are valid, else provide default values...
            // Default :: [Inception Date = {NOW}]
            $movement_date = (Common::validDate($check_date)) ? Carbon::parse($check_date) : Carbon::now();

            // Get the list of movement records that match the specific date
            $check_movements = ($payroll_era == "aa") ?
                    $this->movements()->where('aa_code', '<>', null)->where('date_start', '<=', $movement_date)->where('date_end', '>=', $movement_date) :
                    $this->movements()->where('aa_code', null)->where('date_start', '<=', $movement_date)->where('date_end', '>=', $movement_date);

            if ($check_movements->exists()) {
                // There is a valid movement record under the specified date
                // Return the last() record in the collection, sorted by date_start...
                $movement = $check_movements->orderBy('date_end', 'desc')->first();
                return [
                    'movement' => $movement,
                    'date' => $movement->date_end // Why are we not returning $check_date???
                ];
            } else {
                // There is no valid movement records under the specified date
                // Lets approximate the earliest or latest possible time for this movement record query...
                $first_movement = ($payroll_era == "aa") ?
                                $this->movements()->orderBy('date_start', 'asc')->where('aa_code', '<>', null)->first() :
                                $this->movements()->orderBy('date_start', 'asc')->where('aa_code', null)->first();
                $last_movement = ($payroll_era == "aa") ?
                                $this->movements()->orderBy('date_end', 'desc')->where('aa_code', '<>', null)->first() :
                                $this->movements()->orderBy('date_end', 'desc')->where('aa_code', null)->first();

                if ($first_movement && $movement_date->isBefore($first_movement->date_start)) {
                    return [
                        'movement' => $first_movement,
                        'date' => $first_movement->date_start
                    ];
                } else if ($last_movement && $movement_date->isAfter($last_movement->date_end)) {
                    return [
                        'movement' => $last_movement,
                        'date' => $last_movement->date_end
                    ];
                } else {
                    return null;
                }
            }
        } else {
            return null;
        }
    }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getFirstMovementAttribute() { return ($this->movements()->exists()) ? $this->movements()->orderBy('date_end', 'asc')->first() : null; }
    public function getLatestMovementAttribute() { return ($this->movements()->exists()) ? $this->movements()->orderBy('date_end', 'desc')->first() : null; }
    public function getLatestAaMovementAttribute() { return ($this->movements()->exists()) ? $this->movements()->where('aa_code', '<>', null)->orderBy('date_end', 'desc')->first() : null; }
    public function getFirstDayAttribute() { return ($this->movements()->exists()) ? $this->first_movement->date_start : null; }
    public function getLastDayAttribute() {
        if ($this->movements()->exists()) {
            // Associate has at least one movement record(s)
            $co_last_day = Carbon::parse(env('CO_LAST_DAY'));
            $movement_last_day = $this->latest_movement->date_end;
            if ($co_last_day->isSameDay($movement_last_day)) {
                // Associate is still active...
                return false;
            } else {
                // Associate is not active...
                return $movement_last_day;
            }
        } else {
            return true;
        }
    }
    public function getActiveAttribute() { return ($this->movements()->exists()) ? !$this->last_day : false; }
    public function getLatestDesignationAttribute() { return ($this->movements()->exists()) ? $this->latest_movement->designation : null; }
    public function getLatestAaDesignationAttribute() { return ($this->latest_aa_movement) ? $this->latest_aa_movement->designation : null; }
    public function getLfaCodeAttribute() { return ($this->movements()->exists()) ? $this->latest_movement->lfa_code : null; }
    public function getDirectSupervisorAttribute() { return ($this->movements()->exists()) ? $this->latest_movement->reporting_to : null; }
    public function getIsManagerAttribute() {
        return ($lat_des = $this->latest_designation) ? ($lat_des->salesforce_tier == 3 || ($lat_des->salesforce_tier == 2 && ($lat_des->manager_or_agent || $lat_des->manager_or_self))) : false;
    }
    public function getIsManagerAaAttribute() {
        return ($aa_des = $this->latest_aa_designation) ? ($aa_des->salesforce_tier == 3 || ($aa_des->salesforce_tier == 2 && ($aa_des->manager_or_agent || $aa_des->manager_or_self))) : false;
    }
}
