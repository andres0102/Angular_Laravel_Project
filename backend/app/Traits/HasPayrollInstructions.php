<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\LegacyFA\Payroll\Instruction;

trait HasPayrollInstructions
{
    /** ===================================================================================================
     * Eloquent Model Relationships
     *
     * @var array
     */
    public function payroll_instructions() { return $this->hasMany(Instruction::class, 'from_associate_uuid', 'uuid'); }


    /** ===================================================================================================
     * Custom Functions
     *
     */
    public function commission_payee($check_era = null, $check_comm_tier = null, $payroll_batch_year = null, $payroll_batch_month = null)
    {
        $payroll_date = isset($payroll_batch_year, $payroll_batch_month) ? Carbon::parse($payroll_batch_year .'-'. $payroll_batch_month) : Carbon::now()->startOfMonth();

        if ($this->payroll_instructions->where('date_start','<=',$payroll_date)->where('date_end','>=',$payroll_date)->isNotEmpty()) {
            // If there are instruction(s) to redirect payroll
            // Default :: [Payroll ERA = "LFA"] + [Commission Tier = 1]
            $payroll_era = ($check_era && in_array($check_era, ['aa', 'lfa'])) ? $check_era : 'lfa';
            $payroll_tier = ($check_comm_tier && in_array($check_comm_tier, [1, 2, 3])) ? $check_comm_tier : 1;

            $payroll_instruction = $this->payroll_instructions->where('era', $payroll_era)->where('commission_tier', $payroll_tier)->where('date_start','<=',$payroll_date)->where('date_end','>=',$payroll_date);
            return ($payroll_instruction->isNotEmpty()) ? $payroll_instruction->last()->to_associate : $this;
        } else {
            // If there are no instruction(s) to redirect payroll, lets return the original associate..
            return $this;
        }
    }

    public function batch_payee($policy_inception_date, $payroll_batch_year = null, $payroll_batch_month = null)
    {
        $policy_inception_date = Carbon::parse($policy_inception_date);

        if ($this->movements()->exists()) {
            // associate has at least one movement record(s)
            $associate_last_day = $this->last_day;
            if (!$associate_last_day) {
                // associate does not have last_day attribute (associate has not resigned yet), commissions are payable regardless of batch
                return $this;
            } else if ($associate_last_day && $payroll_batch_year && $payroll_batch_month) {
                // associate has a last_day attribute (associate has resigned)..
                // Payroll batch information is provided, so lets check last_day vs cutoff date..
                // Check if associate last_day is after payroll cut_off date..
                $payroll_cutoff_date = Carbon::parse($payroll_batch_year . '-' . $payroll_batch_month . '-01');
                if ($associate_last_day->isBefore($payroll_cutoff_date)) {
                    // associate last_day is before the first day of the payroll month, commissions are not payable..
                    return null;
                } else {
                    // associate last_day is after the first day of the payroll month..
                    // Check for policy inception date vs associate last day
                    return ($policy_inception_date->isBefore($associate_last_day)) ? $this : null;
                }
            } else {
                // associate has a last_day attribute (associate has resigned)..
                // Payroll batch information is not provided..
                // Check for policy inception date vs associate last day
                return ($policy_inception_date->isBefore($associate_last_day)) ? $this : null;
            }
        } else {
            // associate does not have any movement record(s), commissions are not payable regardless of inception date or payroll batch
            \Log::debug('commission_payable() :: ' . $this->name . ' does not have any movement record(s).');
            return null;
        }
    }
}
