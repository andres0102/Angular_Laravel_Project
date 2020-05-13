<?php

namespace App\Traits;

use App\Helpers\Common;
use Carbon\Carbon;
use App\Models\LegacyFA\Associates\{BandingLFA, BandingGI};

trait HasBandings
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function bandings_lfa() { return $this->hasMany(BandingLFA::class, 'associate_uuid', 'uuid'); }
    public function bandings_gi() { return $this->hasMany(BandingGI::class, 'associate_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function query_banding($check_date = null)
    {
        // Lets check if values provided are valid, else provide default values...
        // Default :: [Inception Date = {NOW}] + [Payroll ERA = "LFA"]
        $banding_date = (Common::validDate($check_date)) ? Carbon::parse($check_date) : Carbon::now();
        $banding_lfa = null;
        $banding_gi = null;

        if ($this->bandings_lfa()->exists()) {
            // Associate has at least one banding record in database..
            $check_bandings_lfa = $this->bandings_lfa()->where('date_start', '<=', $banding_date)->where('date_end', '>=', $banding_date);
            // Check if there is valid banding record under the specified date
            if ($check_bandings_lfa->exists()) {
                // Return the last() record in the collection...
                $banding_lfa = $check_bandings_lfa->orderBy('date_end', 'desc')->first();
            } else {
                // There is no valid banding records under the specified date & era query...
                // Lets approximate the earliest or latest possible time for this movement record query...
                $first_banding_lfa = $this->bandings_lfa()->orderBy('date_start', 'asc')->first();
                $last_banding_lfa = $this->bandings_lfa()->orderBy('date_end', 'desc')->first();
                // Test if banding_date is before first record or after last record..
                if ($banding_date->isBefore($first_banding_lfa->date_start)) {
                    // Banding date is before first banding record...
                    $banding_lfa = $first_banding_lfa;
                } else if ($banding_date->isAfter($last_banding_lfa->date_end)) {
                    // Banding date is before last banding record...
                    $banding_lfa = $last_banding_lfa;
                } else {
                    // Banding date is somewhere in the middle...
                    // TODO:: check exact record
                    // For now, since date does not fall in between start & end of any record, lets just use the latest record...
                    $banding_lfa = $last_banding_lfa;
                }
            }
        }

        if ($this->bandings_gi()->exists()) {
            // Associate has at least one banding record in database..
            $check_bandings_gi = $this->bandings_gi()->where('date_start', '<=', $banding_date)->where('date_end', '>=', $banding_date);
            // Check if there is valid banding record under the specified date
            if ($check_bandings_gi->exists()) {
                // Return the last() record in the collection...
                $banding_gi = $check_bandings_gi->orderBy('date_end', 'desc')->first();
            } else {
                // There is no valid banding records under the specified date & era query...
                // Lets approximate the earliest or latest possible time for this movement record query...
                $first_banding_gi = $this->bandings_gi()->orderBy('date_start', 'asc')->first();
                $last_banding_gi = $this->bandings_gi()->orderBy('date_end', 'desc')->first();
                // Test if banding_date is before first record or after last record..
                if ($banding_date->isBefore($first_banding_gi->date_start)) {
                    // Banding date is before first banding record...
                    $selected_banding_gi = $first_banding_gi;
                } else if ($banding_date->isAfter($last_banding_gi->date_end)) {
                    // Banding date is before last banding record...
                    $selected_banding_gi = $last_banding_gi;
                } else {
                    // Banding date is somewhere in the middle...
                    // TODO:: check exact record
                    // For now, since date does not fall in between start & end of any record, lets just use the latest record...
                    $selected_banding_gi = $last_banding_lfa;
                }
            }
        }

        return [
            'banding_lfa' => $banding_lfa ?? null,
            'banding_gi' => $banding_gi ?? null
        ];
    }


    /** ===================================================================================================
     * Custom Attributes
     *
     */
    public function getLatestBandingLfaAttribute() { return ($this->bandings_lfa()->exists()) ? $this->bandings_lfa()->orderBy('date_end', 'desc')->first() : null; }
    public function getLatestBandingGiAttribute() { return ($this->bandings_gi()->exists()) ? $this->bandings_gi()->orderBy('date_end', 'desc')->first() : null; }
}
