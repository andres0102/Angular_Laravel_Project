<?php
namespace App\Helpers;

use App\Helpers\Common;
use Carbon\{Carbon, CarbonInterface};
use Illuminate\Support\Str;
use App\Models\LegacyFA\Associates\{Associate, Movement, ProviderCode};
use App\Models\LegacyFA\Payroll\{FirmCode, PayrollBatch, PayrollFeed, PayrollRecord, PayrollComputation, PayrollAdjustment};
use App\Models\Selections\RefAAPolicyDates;
use App\Models\Selections\LegacyFA\{SelectProvider, SelectPayrollFeedType};
use App\Jobs\PayrollFeedProcessor;
use Rap2hpoutre\FastExcel\FastExcel;

class PayrollHelper
{
    /** ===================================================================================================
     * Functions to generate monthly payroll feed files for batch processing
     *
     */
    public static function generate_feed($era, $provider_alias = null, $filename, $category, $type = null)
    {
        $results = [
            'era' => $era,
            'filename' => $filename,
            'category' => $category,
            'type' => $type,
            'provider' => $provider_alias
        ];

        if ($era == 'aa' && ($category == 'health' || $category == 'life' || $category == 'trailer')) {
            $results['csv_pipe'] = true;
        } else if (($era == 'aa' && $provider_alias == 'rhi') ||
                   ($era == 'lfa' && ($provider_alias == 'fp' || $provider_alias == 'havenport'))) {
            $results['gst_rate'] = env('CO_GST_RATE');
        }

        return $results;
    }

    /** ===================================================================================================
     * Function to compute commission type
     *
     */
    private static function compute_commission_type($data) {
        if (!isset($data['year'], $data['month'], $data['date_commission']) || !isset($data['date_inception'])) return 'first-year';
        $comm_date = ($data['date_commission']) ?? (Carbon::parse($data['year'] .'-'. $data['month'])->startOfMonth());
        $check_date = (Carbon::parse($data['date_inception'])->startOfMonth());
        return (($comm_date->diffInMonths($check_date)) <= 12) ? 'first-year' : 'renewal';
    }

    /** ===================================================================================================
     * Functions to process commissions feeds
     *
     */
    public static function process_feed(PayrollFeed $feed, $data)
    {
        $feed_type = $feed->feed_type;
        $collection = $data['collection'];

        if ($feed_type->era === 'adjustments') {
            foreach ($collection as $row) {
                if ($row['sl_no']) {
                    $new_record = [];
                    // Batch/Feed information
                    $new_record['year'] = $feed->year;
                    $new_record['month'] = $feed->month;
                    $new_record['batch_id'] = $feed->batch_id;
                    $new_record['payroll_type_slug'] = $feed_type->slug;
                    // Feed/Record information
                    $new_record['feed_id'] = $feed->id;
                    $new_record['payroll_cat_slug'] = $row['category'];
                    $row_provider = SelectProvider::firstAlias($row['provider']);
                    $new_record['provider_slug'] = $row_provider['slug'];
                    // Foreign model references
                    $row_associate = Associate::firstSn($row['sl_no']);
                    $new_record['associate_uuid'] = $row_associate['uuid'];
                    if (Common::validData($row, 'client') && $row['tier'] == 'basic') {
                        $row_client = $row_associate->findOrNewClient($row['client']);
                        $new_record['client_uuid'] = $row_client->uuid;
                    }
                    $new_record['description'] = $row['description'] ?? null;
                    $new_record['tier'] = $row['tier'] ?? 'basic';
                    $new_record['amount'] = (float) $row['amount'];
                    $new_record['date_transaction'] = Carbon::parse($feed->year . '-' . $feed->month)->endOfMonth()->startOfDay();

                    // Add record into database ...
                    $pp_record = $feed->adjustments()->create(array_filter($new_record));
                }
            }

        } else {
            $feed_provider = $feed->provider;
            $fast_excel = $data['fast_excel'];

            // Retrieve mapping record for payroll type ...
            $map = $feed_type->mapping->toArray();

            // Find out which column types are "date" for payroll_feeds_records ...
            $date_columns = [];
            foreach(\DB::connection('lfa_payroll')->getDoctrineSchemaManager()
                       ->listTableColumns('records') as $columns => $data){
                if ($data->getType()->getName() == 'date') array_push($date_columns, $columns);
            }

            foreach ($collection as $row) {
                // apply Str::slug() to field headers
                if ($fast_excel){
                    foreach($row as $key => $value) {
                        $new_key = Str::slug($key, '_');
                        $row[$new_key] = $value;
                    }
                }
                // If there is no "agent_no" mapped field, ignore that row ...
                if ($row[$map['agent_no']]) {
                    // Begin to import row data into database :: Payroll Record ...
                    $row_associate = null;
                    $row_client = null;
                    $row_policy = null;

                    $new_record = [];
                    // Batch/Feed information
                    $new_record['year'] = $feed->year;
                    $new_record['month'] = $feed->month;
                    $new_record['batch_id'] = $feed->batch_id;
                    $new_record['payroll_type_slug'] = $feed_type->slug;
                    $new_record['provider_slug'] = $feed_provider->slug;
                    // Feed/Record information
                    $new_record['feed_id'] = $feed->id;
                    $new_record['payroll_era'] = $feed_type->era;
                    $new_record['payroll_cat_slug'] = ($feed_type->payroll_cat_slug == 'health-life') ? $row['category'] : $feed_type->payroll_cat_slug;

                    // Loop through the map database row to identify what column names to extract,
                    foreach($map as $m_key => $m_value) {
                        // ignore all values that are empty
                        if ($m_value && isset($row[$m_value]) && ($new_value = Common::trimString($row[$m_value]))) {
                            // Check if columns schema is date, and if value is date = convert to date format
                            if (in_array($m_key, $date_columns)) {
                                // Dates columns
                                // \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date_int_val)
                                if (is_numeric($new_value)) $new_value = (int) $new_value;
                                try {
                                    $new_record[$m_key] = Carbon::createFromFormat($feed_type->date_format, $new_value);
                                } catch (\Exception $e) {
                                    if (is_numeric($new_value)) {
                                        $new_record[$m_key] = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($new_value));
                                    } else {
                                        $new_record[$m_key] = null;
                                        \Log::debug($new_value . ' is not in ' . $feed_type->date_format . ' format!!!');
                                        \Log::debug($row);
                                        \Log::debug($e->getMessage());
                                    }
                                }
                            } else {
                                // Non-Dates columns
                                $new_record[$m_key] = $new_value;
                            }
                        }
                    }

                    // Individual providers processing...
                    if ($feed_type->era == 'aa' && $feed_provider->slug == SelectProvider::firstAlias('aviva')->slug) { // ---- AA Health/Life (Basic/OR/Trailer) Commission Feeds
                        // Payment Frequency
                        switch (Common::trimString($row['billing_frequency'])) {
                            case 1:
                                $new_record['payment_frequency'] = 'monthly';
                                break;
                            case 12:
                                $new_record['payment_frequency'] = 'annually';
                                break;
                        }
                        // Premium Type
                        $new_record['premium_type'] = 'regular';
                        // Commission Type
                        if (Common::trimString($row['description']) == 'TRAILER FEE') $new_record['commission_type'] = 'trailer';
                        else $new_record['commission_type'] = 'renewal';
                        // try to replace aa dates from zz_policy_dates old database
                        $temp_date = $new_record['date_inception']->copy();
                        $new_record['date_inception'] = RefAAPolicyDates::isAgent($new_record['agent_no'])->isPolicy($new_record['policy_no'])->oDate($temp_date)->first()->new_date ?? $temp_date;
                        // To avoid confusion, lets over-write the record's commission amount to reflect the FX rate
                        if (isset($new_record['commission_conversion_rate']) && abs((float)$new_record['commission_conversion_rate'] - 1) > 0.01) {
                            $new_record['commission'] /= (float)$new_record['commission_conversion_rate'];
                        }
                        // Calculate Policy Term
                        $policy_inception = $new_record['date_inception'];
                        $policy_expiry = $new_record['date_expiry'];
                        $new_record['policy_term'] = (int) abs($policy_inception->diffInYears($policy_expiry));

                    } else if ($feed_type->era == 'aa' && $feed_provider->slug == SelectProvider::firstAlias('rhi')->slug  && $feed_type->payroll_cat_slug == 'bupa') { // ---- AA BUPA Commission Feeds
                        // Premium Type
                        $new_record['premium_type'] = 'regular';
                        // Commission Type
                        $new_record['commission_type'] = 'bupa';

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('aviva')->slug && $feed_type->payroll_cat_slug == 'health-life') { // ---- LFA AVIVA (Health/Life) Commission Feeds
                        // Product Code & Name
                        $col_name = 'policy_type';
                        $col_code_length = 3;
                        if (substr(Common::trimString($row[$col_name]), $col_code_length, 1) == "-") {
                            $new_record['product_code'] = substr(Common::trimString($row[$col_name]), 0, $col_code_length);
                            $new_record['product_name'] = substr(Common::trimString($row[$col_name]), ($col_code_length + 1));
                        }
                        // Component Code & Name
                        $col_name = 'component';
                        $col_code_length = 4;
                        if (substr(Common::trimString($row[$col_name]), $col_code_length, 1) == "-") {
                            $new_record['component_code'] = substr(Common::trimString($row[$col_name]), 0, $col_code_length);
                            $new_record['component_name'] = substr(Common::trimString($row[$col_name]), ($col_code_length + 1));
                        }
                        // Payment Frequency & Premium Type
                        switch (Common::trimString($row['bil_freq'])) {
                            case 'Monthly':
                                $new_record['payment_frequency'] = 'monthly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Quarterly':
                                $new_record['payment_frequency'] = 'quarterly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Halfyearly':
                                $new_record['payment_frequency'] = 'semi-annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Yearly':
                                $new_record['payment_frequency'] = 'annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Single Prm':
                                $new_record['payment_frequency'] = 'single';
                                $new_record['premium_type'] = 'single';
                                break;
                        }
                        // Commission Type
                        switch (Common::trimString($row['fee_type'])) {
                            case 'First Year Policies':
                                $new_record['commission_type'] = 'first-year';
                                break;
                            case 'Renewal Year Policies':
                                $new_record['commission_type'] = 'renewal';
                                break;
                        }
                        // Date of Commission
                        $new_record['date_commission'] = $new_record['date_due']->copy();
                        // To avoid confusion, lets over-write the record's commission amount to reflect the FX rate
                        if (isset($new_record['commission_conversion_rate']) && abs((float)$new_record['commission_conversion_rate'] - 1) > 0.01) {
                            $new_record['commission'] /= (float)$new_record['commission_conversion_rate'];
                        }
                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('navigator')->slug) { // ---- LFA Navigator Commission Feeds
                        // Payment Frequency
                        $new_record['payment_frequency'] = 'single';
                        // Premium Type
                        $new_record['premium_type'] = 'single';
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);
                        // To avoid confusion, lets over-write the record's commission amount to reflect the FX rate
                        if (isset($new_record['commission_conversion_rate']) && abs((float)$new_record['commission_conversion_rate'] - 1) > 0.01) {
                            $new_record['commission'] /= (float)$new_record['commission_conversion_rate'];
                        }

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('axa')->slug && $feed_type->payroll_cat_slug == 'health-life' && $feed_type->type == 1) { // ---- LFA AXA (Health/Life) Commission Feeds (Feed Type #1)
                        // Payment Frequency
                        switch (Common::trimString($row['payment_mode'])) {
                            case 'M':
                                $new_record['payment_frequency'] = 'monthly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Q':
                                $new_record['payment_frequency'] = 'quarterly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'H':
                                $new_record['payment_frequency'] = 'semi-annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'A':
                                $new_record['payment_frequency'] = 'annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'S':
                                $new_record['payment_frequency'] = 'single';
                                $new_record['premium_type'] = 'single';
                                break;
                        }
                        // Premium
                        $new_record['premium'] = (float)Common::trimString($row['non_linked_premium']) + (float)Common::trimString($row['linked_premium']) + (float)Common::trimString($row['hspa_premium']);
                        // Premium Type
                        if (Common::trimString($row['rp']) == "RP") $new_record['premium_type'] = 'regular';
                        else if (Common::trimString($row['sp']) == "SP") $new_record['premium_type'] = 'single';
                        // Commission Type
                        if (Common::trimString($row['comm_rate_of_ryc'])) $new_record['commission_type'] = 'renewal';
                        else $new_record['commission_type'] = 'first-year';
                        // Date of Commission
                        $col_prod_year = ($row['production_year']) ? (int) Common::trimString($row['production_year']) : $feed->year;
                        $col_prod_month = ($row['production_month']) ? (int) Common::trimString($row['production_month']) : $feed->month;
                        $new_record['date_commission'] = Carbon::parse($col_prod_year .'-'. $col_prod_month)->endOfMonth();

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('axa')->slug && $feed_type->payroll_cat_slug == 'health-life' && $feed_type->type == 2) { // ---- LFA AXA (Health/Life) Commission Feeds (Feed Type #2)
                        // Product Type
                        switch (Common::trimString($row['basic_or_rider_plan'])) {
                            case 'B':
                                $new_record['product_type'] = 'basic';
                                break;
                            case 'R':
                                $new_record['product_type'] = 'rider';
                                break;
                        }
                        // Payment Frequency
                        switch (Common::trimString($row['policy_payment_mode'])) {
                            case 'M':
                                $new_record['payment_frequency'] = 'monthly';
                                break;
                            case 'Q':
                                $new_record['payment_frequency'] = 'quarterly';
                                break;
                            case 'H':
                                $new_record['payment_frequency'] = 'semi-annually';
                                break;
                            case 'A':
                                $new_record['payment_frequency'] = 'annually';
                                break;
                            case 'S':
                                $new_record['payment_frequency'] = 'single';
                                break;
                        }
                        // Premium
                        $new_record['premium'] = (float)Common::trimString($row['non_linked_premium']) + (float)Common::trimString($row['linked_premium']);
                        // Premium Type
                        switch (Common::trimString($row['rpsp'])) {
                            case 'R':
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'S':
                                $new_record['premium_type'] = 'single';
                                break;
                        }
                        // Commission Type
                        if (Common::trimString($row['comm_rate_of_ryc'])) $new_record['commission_type'] = 'renewal';
                        else $new_record['commission_type'] = 'first-year';
                        // Date of Commission
                        $col_prod_year = ($row['year_where_compensation_is_paid']) ? (int) Common::trimString($row['year_where_compensation_is_paid']) : $feed->year;
                        $col_prod_month = ($row['month_where_compensation_is_paid']) ? substr(((int) Common::trimString($row['month_where_compensation_is_paid'])), -2) : $feed->month;
                        $new_record['date_commission'] = Carbon::parse($col_prod_year .'-'. $col_prod_month)->endOfMonth();

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('axa')->slug && $feed_type->payroll_cat_slug == 'trailer' && $feed_type->type == 1) { // ---- LFA AXA Trailer Commission Feeds (Feed Type #1)
                        $new_record['payment_frequency'] = 'trailer';
                        $new_record['premium_type'] = 'trailer';
                        $new_record['commission_type'] = 'trailer';
                        // Date of Commission
                        $col_prod_year = ($row['production_year']) ? (int) Common::trimString($row['production_year']) : $feed->year;
                        $col_prod_month = ($row['production_month']) ? (int) Common::trimString($row['production_month']) : $feed->month;
                        $new_record['date_commission'] = Carbon::parse($col_prod_year .'-'. $col_prod_month)->endOfMonth();

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('axa')->slug && $feed_type->payroll_cat_slug == 'trailer' && $feed_type->type == 2) { // ---- LFA AXA Trailer Commission Feeds (Feed Type #2)
                        $new_record['payment_frequency'] = 'trailer';
                        $new_record['premium_type'] = 'trailer';
                        $new_record['commission_type'] = 'trailer';
                        // Date of Commission
                        $col_prod_year = ($row['production_year']) ? (int) Common::trimString($row['production_year']) : $feed->year;
                        $col_prod_month = ($row['production_month']) ? substr(((int) Common::trimString($row['production_month'])), -2) : $feed->month;
                        $new_record['date_commission'] = Carbon::parse($col_prod_year .'-'. $col_prod_month)->endOfMonth();

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('fp')->slug) { // ---- LFA Friends Provident Commission Feeds
                        // Payment Frequency
                        switch (Common::trimString($row['frequency'])) {
                            case 'M':
                                $new_record['payment_frequency'] = 'monthly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Q':
                                $new_record['payment_frequency'] = 'quarterly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'H':
                                $new_record['payment_frequency'] = 'semi-annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'A':
                                $new_record['payment_frequency'] = 'annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'S':
                                $new_record['payment_frequency'] = 'single';
                                $new_record['premium_type'] = 'single';
                                break;
                        }
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('tm')->slug && $feed_type->payroll_cat_slug == 'life') { // ---- LFA Tokio Marine Commission Feeds
                        // Payment Frequency
                        switch (Common::trimString($row['billingfrequency'])) {
                            case 'Monthly':
                                $new_record['payment_frequency'] = 'monthly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Quarterly':
                                $new_record['payment_frequency'] = 'quarterly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Annual':
                                $new_record['payment_frequency'] = 'annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Single':
                                $new_record['payment_frequency'] = 'single';
                                $new_record['premium_type'] = 'single';
                                break;
                        }
                        // Commission Type
                        switch (Common::trimString($row['subaccount'])) {
                            case 'Initial Commission':
                                $new_record['commission_type'] = 'first-year';
                                break;
                            case 'Renewal Commission':
                                $new_record['commission_type'] = 'renewal';
                                break;
                            case 'Production Override commission':
                                $new_record['commission_type'] = 'firm';
                                break;
                            case 'Group Override commission':
                                // $new_record['commission_type'] = 'group-override';
                                // break;
                                // Skipping this row as the amounts will be covered in a separate GO/PO statement
                                continue 2;
                            default:
                                $new_record['commission_type'] = Common::trimString($row['subaccount']);
                        }

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('tm')->slug && $feed_type->payroll_cat_slug == 'group-override') { // ---- LFA Tokio Marine - Group Overriding Commission Feeds
                        $new_record['payment_frequency'] = 'single';
                        $new_record['premium_type'] = 'group-override';
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('lic')->slug) { // ---- LFA LIC Commission Feeds
                        // Payment Frequency
                        $new_record['payment_frequency'] = 'single';
                        $new_record['premium_type'] = 'single';
                        // Commission Type
                        $col_com_year = (int) Common::trimString($row['com_yr']);
                        if ($col_com_year == 1) $new_record['commission_type'] = 'first-year';
                        else $new_record['commission_type'] = 'renewal';

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('ntuc')->slug && $feed_type->payroll_cat_slug == 'health') { // ---- LFA NTUC (ES/IS) Commission Feeds
                        // Product Code & Name
                        // Check if bracketed value exists
                        if ($col_product = explode("(", rtrim(Common::trimString($row['plan']), ")"))){
                            $new_record['product_code'] = $col_product[1] ?? null;
                            $new_record['product_name'] = $col_product[0] ?? null;
                        }
                        // Payment Frequency
                        $new_record['payment_frequency'] = 'annually';
                        // Premium Type
                        $new_record['premium_type'] = 'regular';
                        // Date of Commission
                        $new_record['date_commission'] = $new_record['date_transaction']->copy();
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('ntuc')->slug && $feed_type->payroll_cat_slug == 'life') { // ---- LFA NTUC (Life) Commission Feeds
                        // Payment Frequency
                        switch (Common::trimString($row['pay_mode'])) {
                            case 'Monthly':
                                $new_record['payment_frequency'] = 'monthly';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Yearly':
                                $new_record['payment_frequency'] = 'annually';
                                $new_record['premium_type'] = 'regular';
                                break;
                            case 'Single':
                                $new_record['payment_frequency'] = 'single';
                                $new_record['premium_type'] = 'single';
                                break;
                        }
                        // Date of Commission
                        $new_record['date_commission'] = (isset($new_record['date_due'])) ? $new_record['date_due']->copy() : $new_record['date_inception']->copy();
                        // Commission Type
                        $col_policy_year = (int) Common::trimString($row['policy_year']);
                        if ($col_policy_year == 1) $new_record['commission_type'] = 'first-year';
                        else $new_record['commission_type'] = 'renewal';

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('havenport')->slug) { // ---- LFA Havenport CIS Commission Feeds
                        // Payment Frequency
                        $new_record['payment_frequency'] = 'single';
                        $new_record['premium_type'] = 'advisory-fee';
                        $new_record['commission_type'] = 'advisory-fee';
                        // Calculate commission
                        $new_record['commission'] = (float)Common::trimString($row['advisory_fee']) + (float)Common::trimString($row['advisory_wrap_fee']) + (float)Common::trimString($row['diy_wrap_fee']) + (float)Common::trimString($row['advisory_administrative_fees']);
                        // Date of Commission
                        $new_record['date_commission'] = (isset($new_record['date_transaction'])) ? $new_record['date_transaction']->copy() : Carbon::parse($new_record['year'] .'-'. $new_record['month'])->startOfMonth();

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('rhi')->slug) { // ---- LFA Raffles Health Commission Feeds
                        // Payment Frequency
                        $new_record['payment_frequency'] = 'annually';
                        $new_record['premium_type'] = 'regular';
                        // Date of Commission
                        $new_record['date_commission'] = Carbon::parse($new_record['year'] .'-'. $new_record['month'])->startOfMonth();
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);

                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('aviva')->slug && $feed_type->payroll_cat_slug == 'ebh') { // ---- LFA AVIVA EBH Commission Feeds
                        // Payment Frequency
                        $new_record['payment_frequency'] = 'annually';
                        $new_record['premium_type'] = 'regular';
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);
                        // Calculate Premium
                        $new_record['premium'] = (float)Common::trimString($row['life_premium']) + (float)Common::trimString($row['anh_premium']);

                    } else if ($feed_type->era == 'gi') { // ---- LFA General Insurance Commission Feeds
                        // Payment Frequency
                        $new_record['payment_frequency'] = 'single';
                        $new_record['premium_type'] = 'single';
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);
                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('manulife')->slug && $feed_type->type == 1) { // ---- LFA Manulife Commission Feeds (Type #1)
                        // Payment Frequency
                        switch (Common::trimString($row['pmt_mode'])) {
                            case '01':
                                $new_record['payment_frequency'] = 'monthly';
                                break;
                            case '12':
                                $new_record['payment_frequency'] = 'annually';
                                break;
                        }
                        // Premium Type
                        if (Common::trimString($row['prem_typ']) == "RP") $new_record['premium_type'] = 'regular';
                        else if (Common::trimString($row['prem_typ']) == "SP") $new_record['premium_type'] = 'single';
                        // Date of Commission
                        $new_record['date_commission'] = $new_record['date_transaction']->copy();
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);
                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('manulife')->slug && $feed_type->type == 2) { // ---- LFA Manulife Commission Feeds (Type #2)
                        // Payment Frequency
                        switch (Common::trimString($row['pmt_mode'])) {
                            case 'Monthly':
                                $new_record['payment_frequency'] = 'monthly';
                                break;
                            case 'Annually':
                                $new_record['payment_frequency'] = 'annually';
                                break;
                        }
                        // Premium Type
                        if (Common::trimString($row['prem_typ']) == "RP") $new_record['premium_type'] = 'regular';
                        else if (Common::trimString($row['prem_typ']) == "SP") $new_record['premium_type'] = 'single';
                        // Date of Commission
                        $new_record['date_commission'] = $new_record['date_transaction']->copy();
                        // Commission Type
                        $new_record['commission_type'] = self::compute_commission_type($new_record);
                    } else if ($feed_type->era == 'lfa' && $feed_provider->slug == SelectProvider::firstAlias('cntaiping')->slug) { // ---- LFA China Taiping Feed
                        // Payment Frequency
                        switch (Common::trimString($row['payment_frequency'])) {
                            case 'Single':
                                $new_record['payment_frequency'] = 'single';
                                break;
                        }
                        // Premium Type
                        switch (Common::trimString($row['premium_type'])) {
                            case 'Single premium':
                                $new_record['premium_type'] = 'single';
                                break;
                        }
                        // Commission Type
                        switch (Common::trimString($row['payment_type'])) {
                            case 'FYC':
                                $new_record['commission_type'] = 'first-year';
                                break;
                            case 'RYC':
                                $new_record['commission_type'] = 'renewal';
                                break;
                        }
                    }

                    // replace all empty strings as NULL
                    foreach($new_record as $r_key => $r_value){ $new_record[$r_key] = Common::trimString($r_value); }
                    $new_record['policy_holder_name'] = Common::trimStringUpper($new_record['policy_holder_name'] ?? null);
                    $new_record['policy_holder_nric'] = Common::trimStringUpper($new_record['policy_holder_nric'] ?? null);
                    $new_record['life_assured_name'] = Common::trimStringUpper($new_record['life_assured_name'] ?? null);
                    $new_record['life_assured_nric'] = Common::trimStringUpper($new_record['life_assured_nric'] ?? null);

                    // PolicyHolder must have a name for us to tag to a client...
                    // Lets replace policy holder name with life assured name (if exists)...
                    if (!isset($new_record['policy_holder_name']) && isset($new_record['life_assured_name'])) {
                        $new_record['policy_holder_name'] = $new_record['life_assured_name'];
                        $new_record['policy_holder_nric'] = $new_record['life_assured_nric'];
                    }

                    // Valid rows = Agent + Inception Date
                    // Also :: Ensure PolicyHolder/PolicyNumber is not null!!
                    $new_record['validated'] = isset($new_record['agent_no'], $new_record['date_inception'], $new_record['policy_holder_name'], $new_record['policy_no']);

                    // Verify agents exists when comparing agent code to database ...
                    $new_record['verified_agent_code'] = false;
                    if (is_numeric($new_record['agent_no'])) $new_record['agent_no'] = (int) $new_record['agent_no'];
                    $new_record['agent_no'] = ($feed_provider->code_length) ? substr(Common::trimString($new_record['agent_no']), 0, $feed_provider->code_length) : $new_record['agent_no'];

                    if ($feed_type->era == 'aa' && Movement::where('aa_code', $new_record['agent_no'])->exists()) {
                        $new_record['verified_agent_code'] = true;
                        $row_associate = Movement::firstAaCode($new_record['agent_no'])->associate;
                        $new_record['associate_uuid'] = $row_associate->uuid;
                    } else if ($feed_type->era == 'lfa' && ProviderCode::continueProviderAlias($feed_provider->alias)->where('code', $new_record['agent_no'])->exists()) {
                        $new_record['verified_agent_code'] = true;
                        $row_associate = ProviderCode::continueProviderAlias($feed_provider->alias)->where('code', $new_record['agent_no'])->first()->associate;
                        $new_record['associate_uuid'] = $row_associate->uuid;
                    } else if ( (FirmCode::continueProviderAlias($feed_provider->alias)->where('code', $new_record['agent_no'])->exists()) || ($feed_type->era == 'gi' && $new_record['agent_no'] == "9999") ) {
                        $new_record['validated'] = true;
                        $new_record['firm_revenue'] = true;
                    } else if ($feed_type->era == 'gi' && $query_associate = Associate::firstSn($new_record['agent_no'])){
                        $new_record['verified_agent_code'] = true;
                        $new_record['validated'] = true;
                        $row_associate = $query_associate;
                        $new_record['associate_uuid'] = $row_associate->uuid;
                    }

                    if ($new_record['validated']) {

                        // Create client & policy relationship record for associate (if possible) ...
                        // Ignore feeds that are 'OR' by nature ...
                        if ($new_record['verified_agent_code'] && $row_associate && $feed_type->type != 'or') {
                            $client_name = $new_record['policy_holder_name'];
                            $client_nric = $new_record['policy_holder_nric'] ?? null;
                            // $row_client = $row_associate->hasClient($client_name, $client_nric);
                            $row_client = $row_associate->findOrNewClientViaPolicy($new_record['provider_slug'], $new_record['policy_no'], $client_name, $client_nric);
                            $new_record['client_uuid'] = $row_client->uuid;

                            // check for life assured
                            $new_record['life_assured_uuid'] = null;
                            if (isset($new_record['life_assured_name'])) {
                                $life_assured = $row_client->findOrNewLifeAssured($new_record['life_assured_name'], $new_record['life_assured_nric']);
                                if (!$life_assured->is($row_client)) $new_record['life_assured_uuid'] = $life_assured->uuid;
                            }

                            // Check if policy record exists ...
                            // Update Policy defails (if any) ...
                            $row_policy = $row_client->findOrNewPolicy($new_record['provider_slug'], $new_record['policy_no'],
                                [
                                    'policy_holder_name' => $new_record['policy_holder_name'],
                                    'policy_holder_nric' => $new_record['policy_holder_nric'] ?? null,
                                    'life_assured_uuid' => $new_record['life_assured_uuid'] ?? null,
                                    'life_assured_name' => $new_record['life_assured_name'] ?? null,
                                    'life_assured_nric' => $new_record['life_assured_nric'] ?? null,
                                    'contract_currency' => $new_record['contract_currency'] ?? null,
                                    'sum_assured' => $new_record['sum_assured'] ?? 0,
                                    'policy_term' => (isset($new_record['policy_term']) && (int) $new_record['policy_term'] >= 0) ? $new_record['policy_term'] : 0,
                                    'premium_term' => (isset($new_record['premium_term']) && (int) $new_record['premium_term'] >= 0) ? $new_record['premium_term'] : 0,
                                    'payment_frequency' => $new_record['payment_frequency'] ?? null,
                                    'date_issued' => $new_record['date_issued'] ?? null,
                                    'date_inception' => $new_record['date_inception'] ?? null,
                                    'date_expiry' => $new_record['date_expiry'] ?? null,
                                    'total_investment' => $new_record['total_investment'] ?? 0,
                                ]);
                            $new_record['policy_uuid'] = $row_policy->uuid;

                            // Create new transaction record for this policy ...
                            $row_transaction = $row_policy->transactions()->create([
                                'year' => $new_record['year'],
                                'month' => $new_record['month'],
                                'payroll_batch_id' => $new_record['batch_id'],
                                'payroll_feed_id' => $new_record['feed_id'],
                                // Transaction information
                                'transaction_no' => $new_record['transaction_no'] ?? null,
                                'transaction_code' => $new_record['transaction_code'] ?? null,
                                'transaction_desc' => $new_record['transaction_desc'] ?? null,
                                'date_transaction' => $new_record['date_transaction'] ?? null,
                                'date_instalment_from' => $new_record['date_instalment_from'] ?? null,
                                'date_instalment_to' => $new_record['date_instalment_to'] ?? null,
                                'date_due' => $new_record['date_due'] ?? null,
                                // Product information
                                'product_code' => $new_record['product_code'] ?? null,
                                'product_type' => $new_record['product_type'] ?? null,
                                'product_name' => $new_record['product_name'] ?? null,
                                'component_code' => $new_record['component_code'] ?? null,
                                'component_name' => $new_record['component_name'] ?? null,
                                 // Premiums/Investment Information
                                'payment_currency' => $new_record['payment_currency'] ?? 'SGD',
                                'premium' => $new_record['premium'] ?? 0,
                                'premium_gst' => $new_record['premium_gst'] ?? 0,
                                'premium_loading' => $new_record['premium_loading'] ?? 0,
                                'premium_conversion_rate' => $new_record['premium_conversion_rate'] ?? 1,
                                'premium_type' => $new_record['premium_type'] ?? 'regular',
                                // Commission information
                                'commission_type' => $new_record['commission_type'] ?? 'regular',
                                'commission_currency' => $new_record['commission_currency'] ?? 'SGD',
                                'commission' => $new_record['commission'] ?? 0,
                                'commission_gst' => $new_record['commission_gst'] ?? 0,
                                'commission_conversion_rate' => $new_record['commission_conversion_rate'] ?? 1,
                            ]);
                            $new_record['policy_transaction_uuid'] = $row_transaction->uuid;
                        }

                        // Calculate Commission w/o GST
                        if (abs($feed->gst_rate) !== 0 && isset($new_record['commission']) && $new_record['commission'] !== 0) {
                            $new_record['commission'] = ((float)$new_record['commission']) / (1.0 + (float)$feed->gst_rate);
                            $new_record['commission_gst'] = $new_record['commission'] * $feed->gst_rate;
                        }
                    }

                    // Add record into database ...
                    // \Log::debug($new_record);
                    $pp_record = $feed->records()->create(array_filter($new_record));
                }
            }
        }
    }


    /** ===================================================================================================
     * Function to process commissions computations for particular record
     *
     */
    public static function process_commissions(PayrollRecord $record) {
      // Payroll default values brought forward by Payroll Record
      $payroll_defaults = [
          'year' => $record->year,
          'month' => $record->month,
          'batch_id' => $record->batch_id,
          'feed_id' => $record->feed_id,
          'record_id' => $record->id,
          'provider_slug' => $record->provider_slug,
          'payroll_era' => $record->payroll_era,
          'payroll_cat_slug' => $record->payroll_cat_slug,
          'payroll_type_slug' => $record->payroll_type_slug,
          'closed_by_uuid' => $record->associate_uuid,
          'client_uuid' => $record->client_uuid,
          'policy_uuid' => $record->policy_uuid,
          'commission_type' => $record->commission_type,
          'policy_transaction_uuid' => $record->policy_transaction_uuid
      ];

      // Lets compute payroll commission
      $record_commission = (float)$record->commission;
      $record_conversion_rate = ($record->commission_conversion_rate) ? (float)$record->commission_conversion_rate : 1.0;
      $payroll_commission = $record_commission * $record_conversion_rate;

      $payroll_remarks = null;

      $tier0_amount = $tier1_amount = $tier2_amount = $tier3_amount = 0;
      $tier1_associate = $tier2_associate = $tier3_associate = null;
      $tier1_payee = $tier2_payee = $tier3_payee = null;

      // Records with firm codes tagged as agent_no
      if ($record->firm_revenue) {
          $record->computations()->create(array_merge($payroll_defaults, [
            'commission_tier' => 0,
            'firm_revenue' => true,
            'amount' => $payroll_commission,
            'remarks' => 'Payroll record(s) directly tagged as firm revenue'
          ]));
          $record->update(['processed' => true]);

      } else if ($record->verified_agent_code) {
          $payroll_type = $record->feed_type;
          $payroll_closer = $record->associate;
          $payroll_rates = self::payroll_rates($payroll_closer, $record->date_inception, $record->year, $record->month, $record->payroll_era);

          $sum_commission = (float)$payroll_commission;

          // Lets process commissions individually as per payroll era
          if ($payroll_type->era == 'aa') {
            // AA Commissions Scheme :: inactive == flow upwards to supervisors
            // Tier 0 commissions == firm
            $sum_basic = $sum_or = 0;
            $tier0_amount = (float)$sum_commission * (float)$payroll_rates['firm']['rate_aa'];

            // Tier 1 commissions for Health & Life Basic, Trailer, BUPA feeds (100% of commissions in record)
            if (in_array($payroll_type->type, ['basic', 'trailer', 'bupa'])) {
              $tier1_amount = ($payroll_type->type == 'bupa') ? ($sum_commission * (7/10)) : $sum_commission;
            }

            // Tier 2/3 or commissions for Health & Life OR, Trailer, BUPA feeds
            if (in_array($payroll_type->type, ['or', 'trailer', 'bupa'])) {
              switch ($payroll_type->type) {
                case 'trailer':
                  $sum_or = (float)$sum_commission / 3;
                  break;
                case 'bupa':
                  $sum_or = (float)$sum_commission * (3/10);
                  break;
                default:
                  $sum_or = (float)$sum_commission;
              }

              $tier2_amount = (float)$sum_or * (float)$payroll_rates['tier2']['or_aa'];
              $tier3_amount = (float)$sum_or * (float)$payroll_rates['tier3']['or_aa'];
            }
            $tier1_payee = $payroll_rates['tier1']['payee_aa'];
            $tier2_payee = $payroll_rates['tier2']['payee_aa'];
            $tier3_payee = $payroll_rates['tier3']['payee_aa'];

          } else if ($payroll_type->era == 'lfa') {
            // LFA Commission Scheme :: inactive == firm revenue
            $tier0_amount = (float)$sum_commission * (float)$payroll_rates['firm']['rate_lfa'];
            $tier1_amount = (float)$sum_commission * (float)$payroll_rates['tier1']['rate_lfa'];
            $tier2_amount = (float)$sum_commission * (float)$payroll_rates['tier2']['or_lfa'];
            $tier3_amount = (float)$sum_commission * (float)$payroll_rates['tier3']['or_lfa'];

            $tier1_payee = $payroll_rates['tier1']['payee_lfa'];
            $tier2_payee = $payroll_rates['tier2']['payee_lfa'];
            $tier3_payee = $payroll_rates['tier3']['payee_lfa'];

            $row_commission = array_sum([$tier0_amount, $tier1_amount, $tier2_amount, $tier3_amount]);
            $payroll_remarks = (abs((float)$payroll_commission - (float)$row_commission) < 0.01) ? null : 'ERROR IN COMMISSION CALCULATION :: ' . $payroll_commission . '--' . $row_commission;

          } else if ($payroll_type->era == 'gi') {
            // GI Commission Scheme :: inactive == firm revenue
            $tier0_amount = (float)$sum_commission * (float)$payroll_rates['firm']['rate_gi'];
            $tier1_amount = (float)$sum_commission * (float)$payroll_rates['tier1']['rate_gi'];
            $tier2_amount = (float)$sum_commission * (float)$payroll_rates['tier2']['or_gi'];
            $tier3_amount = (float)$sum_commission * (float)$payroll_rates['tier3']['or_gi'];

            $tier1_payee = $payroll_rates['tier1']['payee_gi'];
            $tier2_payee = $payroll_rates['tier2']['payee_gi'];
            $tier3_payee = $payroll_rates['tier3']['payee_gi'];

            $row_commission = array_sum([$tier0_amount, $tier1_amount, $tier2_amount, $tier3_amount]);
            $payroll_remarks = (abs((float)$payroll_commission - (float)$row_commission) < 0.01) ? null : 'ERROR IN COMMISSION CALCULATION :: ' . $payroll_commission . '--' . $row_commission;

          }

          // Begin creating computational records...
          // Tier 0 === Firm Revenue
        if ($payroll_type->era != 'aa' && (float)$tier0_amount !== 0) {
          $record->computations()->create(array_merge($payroll_defaults, [
            'commission_tier' => 0,
            'firm_revenue' => true,
            'amount' => (float)$tier0_amount,
            'remarks' => $payroll_remarks
          ]));
        }

          // Tier 1 === Closer
        if (!($payroll_type->era == 'aa' && $payroll_type->type == 'or') && (float)$tier1_amount !== 0) {
          $record->computations()->create(array_merge($payroll_defaults, [
            'commission_tier' => 1,
            'firm_revenue' => !isset($tier1_payee),
            'amount' => (float)$tier1_amount,
            'associate_uuid' => $payroll_rates['tier1']['associate']->uuid,
            'payee_uuid' => ($tier1_payee) ? $tier1_payee->uuid : null,
            'remarks' => $payroll_remarks
          ]));
        }

          // Tier 2 === Manager
        if (!($payroll_type->era == 'aa' && $payroll_type->type == 'basic') && (float)$tier2_amount !== 0) {
            $record->computations()->create(array_merge($payroll_defaults, [
              'commission_tier' => 2,
              'firm_revenue' => !isset($tier2_payee),
              'amount' => (float)$tier2_amount,
              'associate_uuid' => $payroll_rates['tier2']['associate']->uuid,
              'payee_uuid' => ($tier2_payee) ? $tier2_payee->uuid : null,
              'remarks' => $payroll_remarks
            ]));
        }

          // Tier 3 === Manager
        if (!($payroll_type->era == 'aa' && $payroll_type->type == 'basic') && (float)$tier3_amount !== 0) {
          $record->computations()->create(array_merge($payroll_defaults, [
            'commission_tier' => 3,
            'firm_revenue' => !isset($tier3_payee),
            'amount' => (float)$tier3_amount,
            'associate_uuid' => $payroll_rates['tier3']['associate']->uuid,
            'payee_uuid' => ($tier3_payee) ? $tier3_payee->uuid : null,
            'remarks' => $payroll_remarks
          ]));
        }

          // Update Feed Record status...
          $record->update(['processed' => true]);
      }
    }


    /** ===================================================================================================
     * Function to process adjustments computations for particular record
     *
     */
    public static function process_adjustments(PayrollAdjustment $record) {
        // Payroll default values brought forward by Payroll Record
        $payroll_defaults = [
          'year' => $record->year,
          'month' => $record->month,
          'batch_id' => $record->batch_id,
          'feed_id' => $record->feed_id,
          'record_id' => $record->id,
          'record_type' => 'adjustments',
          // 'payroll_era' => 'adjustments',
          'payroll_cat_slug' => $record->payroll_cat_slug,
          'payroll_type_slug' => $record->payroll_type_slug,
          'closed_by_uuid' => $record->associate_uuid,
          'associate_uuid' => $record->associate_uuid,
          'client_uuid' => $record->client_uuid ?? null,
          'amount' => (float) $record->amount,
          'remarks' => $record->description ?? null,
        ];

        $row_personnel = $record->associate;
        $payroll_rates = self::payroll_rates($row_personnel, $record->date_transaction, $record->year, $record->month, 'adjustments');
        $new_computation = [];

        switch ($record->tier) {
            case 'basic':
                $new_computation['commission_tier'] = 1;
                $new_computation['payee_uuid'] = $row_personnel->uuid;
                break;
            case 'or':
                $new_computation['commission_tier'] = $row_personnel->latest_designation->salesforce_tier;
                $row_payee = $payroll_rates['tier'.$row_personnel->latest_designation->salesforce_tier]['payee_lfa'];
                $new_computation['payee_uuid'] = $row_payee->uuid;
                break;
        }

        if (in_array($record->payroll_cat_slug, ['loans', 'wills', 'incentives', 'elite-scheme', 'life'])) {
            $new_computation['payroll_era'] = 'lfa';
            $new_computation['commission_type'] = 'first-year';

        } else {
            $new_computation['payroll_era'] = 'adjustments';
            $new_computation['commission_type'] = 'adjustments';
        }

        $record->computations()->create(array_merge($payroll_defaults, $new_computation));
        // Update Feed Record status...
        $record->update(['processed' => true]);

    }














    /** ===================================================================================================
     * Functions to process associate payroll rates (Firm, Tier1-Banded, Tier2-OR, Tier3-OR)
     *
     */
    public static function payroll_rates(Associate $associate, $check_date = null, $payroll_batch_year = null, $payroll_batch_month = null, $payroll_era = null)
    {
        if ($associate->movements()->doesntExist()) {
            return null;
        } else {
            // Lets check if values provided are valid, else provide default values...
            // Default :: [Inception Date = {NOW}]
            $query_date = (Common::validDate($check_date)) ? Carbon::parse($check_date) : Carbon::now();
            // Check if associate has movement record on {$query_date}...
            // Point to a specific date in movement history where movement record is valid...
            // Mostly effective for backdated / futuredated cases...
            $tier1_query_movement = $associate->query_movement($query_date, $payroll_era);
            // query_movement returns movement + movement date override
            // Check if associate current movement record as per $date_movement, has a Salesforce Designation...
            if ($tier1_movement = $tier1_query_movement['movement']) {
                // associate has at least one movement record(s)
                $movement_date = $tier1_query_movement['date'] ?? $payroll_date;
                $query_banding = $associate->query_banding($check_date);

                // Default firm commission rates
                $default_firm_rate_aa       = 0;
                $default_firm_rate_lfa      = 0.15;
                $default_firm_rate_gi       = 0.25;
                $default_tier1_rate_gi      = 0.6;
                $default_tier2_or_rate_gi   = 0.09;
                $default_tier3_or_rate_gi   = 0.06;
                $default_or_base_aa         = 37;
                $default_or_base_lfa        = 40;
                $default_or_tier2_share     = 15;

                // Process GI rates computation
                $tier1_banding_gi = $query_banding['banding_gi'] ?? null;
                if (isset($tier1_banding_gi)) {
                    switch ($tier1_banding_gi->rank) {
                        case 1:
                            $firm_rate_gi = 0.25;
                            $tier1_rate_gi = 0.6;
                            $tier2_or_rate_gi = 0.09;
                            $tier3_or_rate_gi = 0.06;
                            break;
                        case 2:
                            $firm_rate_gi = 0.19;
                            $tier1_rate_gi = 0.68;
                            $tier2_or_rate_gi = 0.078;
                            $tier3_or_rate_gi = 0.052;
                            break;
                        case 3:
                            $firm_rate_gi = 0.15;
                            $tier1_rate_gi = 0.75;
                            $tier2_or_rate_gi = 0.06;
                            $tier3_or_rate_gi = 0.04;
                            break;
                        case 4:
                            $firm_rate_gi = 0.12;
                            $tier1_rate_gi = 0.8;
                            $tier2_or_rate_gi = 0.048;
                            $tier3_or_rate_gi = 0.032;
                            break;
                        default:
                            $firm_rate_gi = 0.25;
                            $tier1_rate_gi = 0.6;
                            $tier2_or_rate_gi = 0.09;
                            $tier3_or_rate_gi = 0.06;
                    }
                }

                // Process rates for Payroll Commission Tier #1 (Closed By)
                $tier1_associate        = $associate;
                $tier1_payee_aa         = $tier1_associate->commission_payee('aa', 1, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                $tier1_payee_lfa        = $tier1_associate->commission_payee('lfa', 1, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                $tier1_payee_gi         = $tier1_associate->commission_payee('gi', 1, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                $tier1_banding_lfa      = $query_banding['banding_lfa'];
                $tier1_designation      = $tier1_movement->designation;
                $tier1_rate_aa          = 1.0;
                $tier1_rate_lfa         = ($tier1_banding_lfa) ? $tier1_banding_lfa->rate : null;

                // Different computational process for various salesforce_tier for Tier 1 associate...
                switch ($tier1_designation->salesforce_tier) {
                    case 1:
                    case 2:
                        $tier2_associate            = ($tier1_designation->salesforce_tier == 1) ? $tier1_movement->reporting_to : $tier1_associate;
                        $tier2_payee_aa             = $tier2_associate->commission_payee('aa', 2, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier2_payee_lfa            = $tier2_associate->commission_payee('lfa', 2, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier2_payee_gi             = $tier2_associate->commission_payee('gi', 2, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier2_movement             = ($tier1_associate->is($tier2_associate)) ? $tier1_movement : $tier2_associate->query_movement($movement_date, $payroll_era)['movement'];
                        $tier2_designation          = $tier2_movement->designation;
                        if ($tier2_designation->salesforce_tier == 2) {
                            // Direct supervisor is tier 2
                            $tier2_or_factor        = ($tier1_associate->is($tier2_associate)) ? $tier2_designation->manager_or_self : $tier2_designation->manager_or_agent;
                            $tier3_associate        = $tier2_movement->reporting_to ?? $tier2_associate;
                        } else if ($tier2_designation->salesforce_tier == 3) {
                            // Direct supervisor is tier 3
                            $tier2_or_factor        = false;
                            $tier3_associate        = $tier2_associate;
                        }

                        if (!$tier3_associate) {
                            \Log::debug([
                                'associate' => $tier1_associate->lfa_sl_no,
                                'date' => $movement_date
                            ]);
                        }

                        // As associate is not tier 3 by default
                        // Compute Tier 3 associate & payee
                        $tier3_payee_aa             = $tier3_associate->commission_payee('aa', 3, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier3_payee_lfa            = $tier3_associate->commission_payee('lfa', 3, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier3_payee_gi             = $tier3_associate->commission_payee('gi', 3, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);

                        if ($tier2_or_factor) {
                            // Tier 2 associate is entitiled to overriding commissions
                            // Compute tier 2 rates
                            $tier2_or_rate_aa       = ($tier2_or_factor / $default_or_base_aa);
                            $tier2_or_rate_lfa      = (1.0 - $default_firm_rate_lfa - $tier1_rate_lfa) * ($tier2_or_factor / $default_or_base_lfa);
                            // Compute tier 3 rates
                            $tier3_or_rate_aa       = (1.0 - $tier2_or_rate_aa);
                            $tier3_or_rate_lfa      = (1.0 - $default_firm_rate_lfa - $tier1_rate_lfa - $tier2_or_rate_lfa);
                        } else {
                            // Tier 2 associate is not entitiled to overriding commissions
                            // Compute tier 2 rates :: Tier 2 will be zero, as all forwarded to tier 3
                            $tier2_or_rate_aa       = 0;
                            $tier2_or_rate_lfa      = 0;
                            $tier2_payee_aa         = $tier3_payee_aa;
                            $tier2_payee_lfa        = $tier3_payee_lfa;
                            $tier2_payee_gi         = $tier3_payee_gi;
                            // Compute tier 3 rates :: Inherited all overriding rates from tier 2
                            $tier3_or_rate_aa       = 1.0;
                            $tier3_or_rate_lfa      = (1.0 - $default_firm_rate_lfa - $tier1_rate_lfa);
                            // Re-compute gi rates :: Tier 2 will be zero, as all forwarded to tier 3
                            $tier2_or_rate_gi       = 0;
                            $tier3_or_rate_gi       = 1.0 - ($firm_rate_gi ?? $default_firm_rate_gi) - ($tier1_rate_gi ?? $default_tier1_rate_gi);
                        }
                        break;

                    case 3:
                        // associate is a Tier 3 :: Director (no need to process, directors get all overrides in this case)
                        // Compute payees (inherit all from tier 1 computations, therefore no computations is required)
                        $tier2_associate = $tier3_associate = $tier1_associate;
                        $tier2_payee_aa = $tier2_associate->commission_payee('aa', 2, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier3_payee_aa = $tier3_associate->commission_payee('aa', 3, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier2_payee_lfa = $tier2_associate->commission_payee('lfa', 2, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier3_payee_lfa = $tier3_associate->commission_payee('lfa', 3, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier2_payee_gi = $tier2_associate->commission_payee('gi', 2, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        $tier3_payee_gi = $tier3_associate->commission_payee('gi', 3, $payroll_batch_year, $payroll_batch_month)->batch_payee($check_date, $payroll_batch_year, $payroll_batch_month);
                        // Compute tier 2 rates
                        $tier2_or_rate_aa           = 0;
                        $tier2_or_rate_lfa          = 0;
                        $tier2_or_rate_gi           = 0;
                        // Compute tier 3 rates
                        $tier3_or_rate_aa           = 1.0;
                        $tier3_or_rate_lfa          = (1.0 - $default_firm_rate_lfa - $tier1_rate_lfa);
                        $tier3_or_rate_gi           = 1.0 - ($firm_rate_gi ?? $default_firm_rate_gi) - ($tier1_rate_gi ?? $default_tier1_rate_gi);
                        break;

                    default:
                        \Log::debug('payroll_rates() -> designation -> salesforce_tier :: ' . $associate->name . ' does not have a valid salesforce tier.');
                        return null;
                }

                // For AA, if Tier 2 is not batch payable, direct payments to Tier 3
                if (!$tier2_payee_aa) {
                    $tier2_payee_aa = $tier3_payee_aa;
                    $tier2_or_rate_aa = 0;
                    $tier3_or_rate_aa = 1.0;
                }

                // Format data and return results in formatted array...
                return [
                    'firm' => [
                        'rate_aa'       => (float) $default_firm_rate_aa,
                        'rate_lfa'      => (float) $default_firm_rate_lfa,
                        'rate_gi'       => (float) ($firm_rate_gi ?? $default_firm_rate_gi),
                    ],
                    'tier1' => [
                        'associate'     => $associate,
                        'payee_aa'      => $tier1_payee_aa,
                        'payee_lfa'     => $tier1_payee_lfa,
                        'payee_gi'      => $tier1_payee_gi,
                        'rate_aa'       => (float) $tier1_rate_aa,
                        'rate_lfa'      => (float) $tier1_rate_lfa,
                        'rate_gi'       => (float) ($tier1_rate_gi ?? $default_tier1_rate_gi),
                    ],
                    'tier2' => [
                        'associate'     => $tier2_associate,
                        'payee_aa'      => $tier2_payee_aa,
                        'payee_lfa'     => $tier2_payee_lfa,
                        'payee_gi'      => $tier2_payee_gi,
                        'or_aa'         => (float) $tier2_or_rate_aa,
                        'or_lfa'        => (float) $tier2_or_rate_lfa,
                        'or_gi'         => (float) ($tier2_or_rate_gi ?? $default_tier2_or_rate_gi),
                    ],
                    'tier3' => [
                        'associate'     => $tier3_associate,
                        'payee_aa'      => $tier3_payee_aa,
                        'payee_lfa'     => $tier3_payee_lfa,
                        'payee_gi'      => $tier3_payee_gi,
                        'or_aa'         => (float) $tier3_or_rate_aa,
                        'or_lfa'        => (float) $tier3_or_rate_lfa,
                        'or_gi'         => (float) ($tier3_or_rate_gi ?? $default_tier3_or_rate_gi),
                    ]
                ];
            } else {
                // associate either has movement error or designation error...
                \Log::debug('PayrollHelper::payroll_rates() --- Error in retrieving movement/designation for ' . $associate->name);
            }
        }
    }






































    /** ===================================================================================================
     * Functions to generate monthly payroll feed files for batch processing
     *
     */
    public static function payroll_statement(Associate $associate, $year = null, $month = null)
    {
        $payroll_batch = null;

        if (isset($year, $month)) {
            // Return payroll commissions for selected payroll batch
            if ($payroll_batch = PayrollBatch::where('year', $year)->where('month', $month)->first()) {
                // Return generated payroll statement...
                return self::generate_payroll($associate, $payroll_batch);
                // return cache()->rememberForever(
                //     'payroll-cache---' . $year . '-' . $month . '-' . $associate->lfa_sl_no,
                //     function () use ($payroll_batch) { return self::generate_payroll($associate, $payroll_batch); });
            } else {
                // Variable(s) error // there is no payroll batch at all
                \Log::debug('payroll_statement() :: There is no payroll batch available under selected ' . $year . ' and ' . $month . '...');
                return null;
            }
        }
    }

    private static function generate_payroll(Associate $associate, PayrollBatch $payroll_batch = null)
    {
        if (!isset($payroll_batch)) $payroll_batch = PayrollBatch::orderBy('year', 'desc')->orderBy('month', 'desc')->first();
        $year = $payroll_batch->year;
        $month = $payroll_batch->month;
        $ytd_months_array = Common::ytdMonthsArray($month);
        $months_array = Common::monthsArray();
        $month_name = $months_array[($month - 1)];
        $pdf_name = $associate->uuid . '-' . $year . '-' . Common::trimStringUpper($month_name) . '.pdf';

        // Obtain MTD and YTD computations relations anchor
        $computations_mtd = $associate->payroll_computations()->where('batch_id', $payroll_batch->id);
        $computations_ytd = $associate->payroll_computations()->where('year', $year)->whereIn('month', $ytd_months_array);
        if ($computations_mtd->doesntExist() || $computations_ytd->doesntExist()) return null;

        // Managers
        if ($associate->is_manager || $associate->is_manager_aa) {
            // Active Agents
            $sales_agents_summary = [];
            $resigned_sales_agents_summary = [];
            // Add associate into their own OR list
            array_push($sales_agents_summary, self::payroll_or_array($associate, $associate, $payroll_batch));
            // Add active sales agents
            $accounted_agents = collect($sales_agents_summary)->pluck('uuid');
            if ($active_agents = $associate->active_sales_agents(Carbon::parse($year.'-'.$month)->endOfMonth())) {
                foreach ($active_agents->sortBy('first_day')->filter(function ($value, $key) use ($associate) { return $value['uuid'] != $associate->uuid; }) as $agent) {
                    if (!in_array($agent->uuid, $accounted_agents->all())) {
                        array_push($sales_agents_summary, self::payroll_or_array($associate, $agent, $payroll_batch));
                    }
                }
            }
            // Lets identify agents who contributed to overriding commissions, but not in the array above...
            $accounted_agents = collect($sales_agents_summary)->pluck('uuid');
            foreach ((clone $computations_ytd)->whereNotIn('closed_by_uuid', $accounted_agents)->where('associate_uuid', $associate->uuid)->whereIn('payroll_era', ['aa','gi','lfa'])->whereIn('commission_tier', [2,3])->distinct()->get('closed_by_uuid') as $or_comp) {
                array_push($resigned_sales_agents_summary, self::payroll_or_array($associate, $or_comp->closed_by, $payroll_batch, true));
            }
        }

        $inherit_sales_agents_summary = [];
        foreach ((clone $computations_ytd)->where('associate_uuid', '<>', $associate->uuid)->where('payroll_era', '<>', 'adjustments')->whereIn('commission_tier', [2,3])->distinct()->get('associate_uuid') as $inherit_comp) {
            $inherit_agent = $inherit_comp->associate;
            array_push($inherit_sales_agents_summary, [
                'name' => $inherit_agent->name,
                'date_resigned' => ($inherit_agent->date_resigned) ? $inherit_agent->date_resigned->format('Y-m-d') : "-",
                'last_day' => ($inherit_agent->last_day) ? $inherit_agent->last_day->format('Y-m-d') : "-",
                'or_mtd' => [
                    'total' => $computations_mtd->where('associate_uuid', $inherit_agent->uuid)->where('payroll_era', '<>', 'adjustments')->whereIn('commission_tier', [2,3])->sum('amount'),
                ],
                'or_ytd' => [
                    'total' => $computations_ytd->where('associate_uuid', $inherit_agent->uuid)->where('payroll_era', '<>', 'adjustments')->whereIn('commission_tier', [2,3])->sum('amount'),
                ]
            ]);
        }

        $payroll_rates = self::payroll_rates($associate);
        $payroll_commission = self::payroll_commission($associate, $payroll_batch);

        return [
            'show_agent_ytd'                => true,
            'payroll_year'                  => $year,
            'payroll_month'                 => $month_name,
            'support_email'                 => env('CO_SUPPORT_EMAIL'),
            'support_no'                    => env('CO_SUPPORT_NO'),
            'filename'                      => $pdf_name,
            'associate' => [
                'name'                      => $associate->name,
                'lfa_email'                 => $associate->lfa_email,
                'lfa_code'                  => $associate->lfa_code,
                'has_override'              => $associate->is_manager,
                'has_aa'                    => ($associate->latest_aa_movement !== null),
                'has_aa_override'           => $associate->is_manager_aa,
                'designation'               => ($associate->latest_designation) ? $associate->latest_designation->title : null,
                'designation_aa'            => ($associate->latest_aa_designation) ? $associate->latest_aa_designation->title : null,
                'salesforce_tier'           => ($associate->latest_designation) ? $associate->latest_designation->salesforce_tier : null,
                'salesforce_tier_aa'        => ($associate->latest_aa_designation) ? $associate->latest_aa_designation->salesforce_tier : null,
                'banding_lfa'               => $associate->latest_banding_lfa,
                'rate_lfa'                  => $payroll_rates['tier1']['rate_lfa'] ?? 0,
                'banding_gi'                => $associate->latest_banding_gi,
                'rate_gi'                   => $payroll_rates['tier1']['rate_gi'] ?? 0,
                'first_day'                 => ($associate->first_day) ? $associate->first_day->format('Y-m-d') : null,
                'last_day'                  => ($associate->last_day) ? $associate->last_day->format('Y-m-d') : null,
                'supervisor'                => ($associate->direct_supervisor) ? $associate->direct_supervisor->name : null,
            ],
            'payroll' => [
                'summary' => [
                    'payroll_year'          => $payroll_batch->year,
                    'payroll_month'         => $payroll_batch->month,
                    'commission'            => $payroll_commission,
                    'agents'                => $sales_agents_summary ?? [],
                    'resigned_agents'       => (isset($resigned_sales_agents_summary)) ? collect($resigned_sales_agents_summary)->sortByDesc(function($rec) { return $rec['or_ytd']['total']; }) : [],
                    'inherit_agents'        => (isset($inherit_sales_agents_summary)) ? collect($inherit_sales_agents_summary)->sortByDesc(function($rec) { return $rec['or_ytd']['total']; }) : [],
                    'basic_client_count'    => $computations_mtd->whereIn('payroll_era', ['lfa', 'gi'])->where('commission_tier', 1)->distinct('client_uuid')->count(),
                    'basic_policy_count'    => $computations_mtd->whereIn('payroll_era', ['lfa', 'gi'])->where('commission_tier', 1)->distinct('policy_uuid')->count(),
                    'basic_client_count_aa' => $computations_mtd->where('payroll_era', 'aa')->where('commission_tier', 1)->distinct('client_uuid')->count(),
                    'basic_policy_count_aa' => $computations_mtd->where('payroll_era', 'aa')->where('commission_tier', 1)->distinct('policy_uuid')->count(),
                    // 'production' => [
                    //     'categories'        => $production_by_cat,
                    //     'ape'               => null,
                    //     'fyc'               => null,
                    //     'afyc'              => null,
                    //     'fyp'               => null,
                    //     'afyp'              => null,
                    // ],
                    'payroll_era' => [
                        'aviva-advisers' => [
                            'mtd'               => self::payroll_era_summary($associate, clone $computations_mtd, 'aa'),
                            'ytd'               => self::payroll_era_summary($associate, clone $computations_ytd, 'aa'),
                        ],
                        'legacy-fa' => [
                            'mtd'               => self::payroll_era_summary($associate, clone $computations_mtd, 'lfa'),
                            'ytd'               => self::payroll_era_summary($associate, clone $computations_ytd, 'lfa'),
                        ]
                    ]
                ],
                // 'computations' => [
                //     'basic'                     => $basic_computations_by_cat,
                //     'adjustments' => [
                //         'basic'                 => collect($payroll_by_era_mtd['adjustments'] ?? [])->where('commission_type', 'adjustment-basic'),
                //         'or'                    => collect($payroll_by_era_mtd['adjustments'] ?? [])->where('commission_type', 'adjustment-or'),
                //         'elite'                 => collect($payroll_by_era_mtd['adjustments'] ?? [])->where('commission_type', 'adjustment-elite'),
                //     ]
                // ]
            ]
        ];
    }

    private static function payroll_era_summary(Associate $associate, $computation, $era)
    {
        $filtered_era = (clone $computation)->where('payroll_era', $era);
        $filtered_era_pers = (clone $filtered_era)->where('associate_uuid', $associate->uuid);

        if ($era == 'aa') {
            $basic_array = [
                'total'         => (float)(clone $filtered_era)->where('commission_tier', 1)->sum('amount'),
                'first-year'    => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','first-year')->sum('amount'),
                'renewal'       => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','renewal')->sum('amount'),
                'trailer'       => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','trailer')->sum('amount'),
                'bupa'          => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','bupa')->sum('amount'),
            ];

            $or_array = [
                'total'         => (float)(clone $filtered_era)->whereIn('commission_tier', [2, 3])->sum('amount'),
                'unit'          => (float)(clone $filtered_era_pers)->where('commission_tier', 2)->sum('amount'),
                'group'         => (float)(clone $filtered_era_pers)->where('commission_tier', 3)->sum('amount'),
                'inherit'       => (float)(clone $filtered_era)->whereIn('commission_tier', [2, 3])->where('associate_uuid', '<>', $associate->uuid)->sum('amount'),
            ];
        } else if ($era == 'lfa') {
            $lfa_with_gi = (clone $computation)->whereIn('payroll_era', ['lfa', 'gi']);
            $lfa_with_gi_pers = (clone $lfa_with_gi)->where('associate_uuid', $associate->uuid);
            $basic_array = [
                'total'             => (float)(clone $lfa_with_gi)->where('commission_tier', 1)->sum('amount'),
                'first-year'        => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','first-year')->sum('amount'),
                'renewal'           => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','renewal')->sum('amount'),
                'trailer'           => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','trailer')->sum('amount'),
                'advisory-fee'      => (float)(clone $filtered_era_pers)->where('commission_tier', 1)->where('commission_type','advisory-fee')->sum('amount'),
                'general-insurance' => (float)(clone $lfa_with_gi)->where('commission_tier', 1)->where('payroll_era','gi')->sum('amount'),
            ];

            $or_array = [
                'total'             => (float)(clone $lfa_with_gi)->whereIn('commission_tier', [2, 3])->sum('amount'),
                'unit'              => (float)(clone $lfa_with_gi_pers)->where('commission_tier', 2)->sum('amount'),
                'group'             => (float)(clone $lfa_with_gi_pers)->where('commission_tier', 3)->sum('amount'),
                'inherit'           => (float)(clone $lfa_with_gi)->whereIn('commission_tier', [2, 3])->where('associate_uuid', '<>', $associate->uuid)->sum('amount'),
            ];
        }

        return [
            'total_comm'    => (float)(clone $filtered_era)->sum('amount'),
            'basic'         => $basic_array,
            'or'            => $or_array
        ];
    }

    private static function payroll_or_array(Associate $associate, associate $agent, PayrollBatch $payroll_batch, $resigned = false)
    {
        $year = $payroll_batch->year;
        $month = $payroll_batch->month;
        $ytd_months_array = Common::ytdMonthsArray($month);

        // Obtain MTD and YTD computations relations anchor
        $basic_computations_mtd = $agent->payroll_computations()->where('batch_id', $payroll_batch->id);
        $basic_computations_ytd = $agent->payroll_computations()->where('year', $year)->whereIn('month', $ytd_months_array);
        $or_computations_mtd = PayrollComputation::where('closed_by_uuid', $agent->uuid)->where('associate_uuid', $associate->uuid)->where('payee_uuid', $associate->uuid)->where('batch_id', $payroll_batch->id);
        $or_computations_ytd = PayrollComputation::where('closed_by_uuid', $agent->uuid)->where('associate_uuid', $associate->uuid)->where('payee_uuid', $associate->uuid)->where('year', $year)->whereIn('month', $ytd_months_array);

        if ($resigned) {
            $views_array = $mtd_array = $ytd_array = null;
        } else {
            $agent_commission = self::payroll_commission($agent, $payroll_batch);
            $views_array = [
                'aviva-advisers' => [
                    'basic' => ($agent_commission['basic']['mtd']['gross-commission-aa'] !== 0 || $agent_commission['basic']['ytd']['gross-commission-aa'] !== 0) ?? false,
                    'or' => ($agent_commission['or']['mtd']['gross-commission-aa'] !== 0 || $agent_commission['or']['ytd']['gross-commission-aa'] !== 0) ?? false,
                ],
                'legacy-fa' => [
                    'basic' => ($agent_commission['basic']['mtd']['gross-commission-lfa'] !== 0 || $agent_commission['basic']['ytd']['gross-commission-lfa'] !== 0) ?? false,
                    'or' => ($agent_commission['or']['mtd']['gross-commission-lfa'] !== 0 || $agent_commission['or']['ytd']['gross-commission-lfa'] !== 0) ?? false,
                    'adjustments' => ($agent_commission['total']['mtd']['nett-adjustments'] !== 0 || $agent_commission['total']['ytd']['nett-adjustments'] !== 0) ?? false,
                    'elite' => ($agent_commission['elite']['mtd'] !== 0 || $agent_commission['elite']['ytd'] !== 0) ?? false,
                ],
            ];
            $mtd_array = [
                'total' => $agent_commission['total']['mtd']['nett-commission'],
                'lfa-basic' => $agent_commission['basic']['mtd']['gross-commission-lfa'],
                'lfa-or' => $agent_commission['or']['mtd']['gross-commission-lfa'],
                'aa-basic' => $agent_commission['basic']['mtd']['gross-commission-aa'],
                'aa-or' => $agent_commission['or']['mtd']['gross-commission-aa'],
                'adjustments' => $agent_commission['total']['mtd']['nett-adjustments'],
                'elite' => $agent_commission['elite']['mtd'],
            ];
            $ytd_array = [
                'total' => $agent_commission['total']['ytd']['nett-commission'],
                'lfa-basic' => $agent_commission['basic']['ytd']['gross-commission-lfa'],
                'lfa-or' => $agent_commission['or']['ytd']['gross-commission-lfa'],
                'aa-basic' => $agent_commission['basic']['ytd']['gross-commission-aa'],
                'aa-or' => $agent_commission['or']['ytd']['gross-commission-aa'],
                'adjustments' => $agent_commission['total']['ytd']['nett-adjustments'],
                'elite' => $agent_commission['elite']['ytd'],
            ];
        }

        return [
            'uuid' => $agent->uuid,
            'name' => $agent->name,
            'lfa_code' => $agent->lfa_code,
            'designation' => ($agent->latest_designation) ? $agent->latest_designation->title : null,
            'first_day' => ($agent->first_day) ? $agent->first_day->format('Y-m-d') : null,
            'last_day' => ($agent->last_day) ? $agent->last_day->format('Y-m-d') : null,
            'date_resigned' => ($agent->date_resigned) ? $agent->date_resigned->format('Y-m-d') : null,
            'length' => ($a_l_d = $agent->last_day) ? $a_l_d->diffForHumans($agent->first_day, CarbonInterface::DIFF_ABSOLUTE) : Carbon::now()->diffForHumans($agent->first_day, CarbonInterface::DIFF_ABSOLUTE),
            'views' => $views_array,
            'mtd' => $mtd_array,
            'ytd' => $ytd_array,
            'or_mtd' => [
                'total' => (clone $or_computations_mtd)->whereIn('payroll_era', ['aa', 'gi', 'lfa'])->whereIn('commission_tier', [2,3])->sum('amount'),
                'lfa' => (clone $or_computations_mtd)->whereIn('payroll_era', ['gi', 'lfa'])->whereIn('commission_tier', [2,3])->sum('amount'),
                'aa' => (clone $or_computations_mtd)->where('payroll_era', 'aa')->whereIn('commission_tier', [2,3])->sum('amount'),
            ],
            'or_ytd' => [
                'total' => (clone $or_computations_ytd)->whereIn('payroll_era', ['aa', 'gi', 'lfa'])->whereIn('commission_tier', [2,3])->sum('amount'),
                'lfa' => (clone $or_computations_ytd)->whereIn('payroll_era', ['gi', 'lfa'])->whereIn('commission_tier', [2,3])->sum('amount'),
                'aa' => (clone $or_computations_ytd)->where('payroll_era', 'aa')->whereIn('commission_tier', [2,3])->sum('amount'),
            ]
        ];
    }

    private static function payroll_commission(associate $agent, PayrollBatch $payroll_batch)
    {
        $year = $payroll_batch->year;
        $month = $payroll_batch->month;
        $ytd_months_array = Common::ytdMonthsArray($month);

        // Obtain MTD and YTD computations relations anchor
        $computations_mtd = $agent->payroll_computations()->where('batch_id', $payroll_batch->id);
        $computations_ytd = $agent->payroll_computations()->where('year', $year)->whereIn('month', $ytd_months_array);

        // Compute Carry Forward Adjustments...
        $mtd_cf = self::payroll_commission_cf_helper($computations_mtd);
        $ytd_cf = [
            'total_nett_adjustment' => 0,
            'total_nett_commission' => 0,
            'basic_nett_adjustment' => 0,
            'basic_nett_commission' => 0,
            'or_nett_adjustment' => 0,
            'or_nett_commission' => 0
        ];
        foreach ($ytd_months_array as $ytd_month) {
            $temp_computations_mtd = $agent->payroll_computations()->where('year', $year)->where('month', $ytd_month);
            if ($temp_mtd_cf = self::payroll_commission_cf_helper($temp_computations_mtd)) {
                $ytd_cf['total_nett_adjustment'] += (float)$temp_mtd_cf['total_nett_adjustment_mtd'];
                $ytd_cf['total_nett_commission'] += (float)$temp_mtd_cf['total_nett_commission_mtd'];
                $ytd_cf['basic_nett_adjustment'] += (float)$temp_mtd_cf['basic_nett_adjustment_mtd'];
                $ytd_cf['basic_nett_commission'] += (float)$temp_mtd_cf['basic_nett_commission_mtd'];
                $ytd_cf['or_nett_adjustment'] += (float)$temp_mtd_cf['or_nett_adjustment_mtd'];
                $ytd_cf['or_nett_commission'] += (float)$temp_mtd_cf['or_nett_commission_mtd'];
            }
        }

        return [
            'total' => [
                'mtd' => [
                    'gross-adjustments' => (float)(clone $computations_mtd)->where('payroll_era', 'adjustments')->whereIn('commission_type', ['adjustment-basic', 'adjustment-or'])->sum('amount'),
                    'nett-adjustments' => (float)$mtd_cf['total_nett_adjustment_mtd'],
                    'gross-commission' => (float)(clone $computations_mtd)->whereIn('payroll_era', ['aa', 'lfa', 'gi'])->sum('amount'),
                    'nett-commission' => (float)$mtd_cf['total_nett_commission_mtd'],
                ],
                'ytd' => [
                    'gross-adjustments' => (float)(clone $computations_ytd)->where('payroll_era', 'adjustments')->whereIn('commission_type', ['adjustment-basic', 'adjustment-or'])->sum('amount'),
                    'nett-adjustments' => (float)$ytd_cf['total_nett_adjustment'],
                    'gross-commission' => (float)(clone $computations_ytd)->whereIn('payroll_era', ['aa', 'lfa', 'gi'])->sum('amount'),
                    'nett-commission' => (float)$ytd_cf['total_nett_commission'],
                ],
            ],
            'basic' => [
                'mtd' => [
                    'nett-commission' => (float)$mtd_cf['basic_nett_commission_mtd'],
                    'gross-commission-aa' => (float)$mtd_cf['basic_commission_aa_mtd'],
                    'gross-commission-lfa' => (float)(clone $computations_mtd)->whereIn('payroll_era', ['lfa', 'gi'])->where('commission_tier', 1)->sum('amount'),
                    'gross-adjustments' => (float)$mtd_cf['basic_adjustment_mtd'],
                    'nett-adjustments' => (float)$mtd_cf['basic_nett_adjustment_mtd'],
                    'carry-forward-adjustments' => (float)$mtd_cf['basic_cf_adjustment_mtd'],
                ],
                'ytd' => [
                    'nett-commission' => (float)$ytd_cf['basic_nett_commission'],
                    'gross-commission-aa' => (float)(clone $computations_ytd)->whereIn('payroll_era', ['aa'])->where('commission_tier', 1)->sum('amount'),
                    'gross-commission-lfa' => (float)(clone $computations_ytd)->whereIn('payroll_era', ['lfa', 'gi'])->where('commission_tier', 1)->sum('amount'),
                    'gross-adjustments' => (float)(clone $computations_ytd)->where('payroll_era', 'adjustments')->where('commission_type', 'adjustment-basic')->sum('amount'),
                    'nett-adjustments' => (float)$ytd_cf['basic_nett_adjustment'],
                ],
            ],
            'or' => [
                'mtd' => [
                    'nett-commission' => (float)$mtd_cf['or_nett_commission_mtd'],
                    'gross-commission-aa' => (float)(clone $computations_mtd)->whereIn('payroll_era', ['aa'])->whereIn('commission_tier', [2, 3])->sum('amount'),
                    'gross-commission-lfa' => (float)(clone $computations_mtd)->whereIn('payroll_era', ['lfa', 'gi'])->whereIn('commission_tier', [2, 3])->sum('amount'),
                    'gross-adjustments' => (float)$mtd_cf['or_adjustment_mtd'],
                    'nett-adjustments' => (float)$mtd_cf['or_nett_adjustment_mtd'],
                    'carry-forward-adjustments' => (float)$mtd_cf['or_cf_adjustment_mtd'],
                ],
                'ytd' => [
                    'nett-commission' => (float)$ytd_cf['or_nett_commission'],
                    'gross-commission-aa' => (float)(clone $computations_ytd)->whereIn('payroll_era', ['aa'])->whereIn('commission_tier', [2, 3])->sum('amount'),
                    'gross-commission-lfa' => (float)(clone $computations_ytd)->whereIn('payroll_era', ['lfa', 'gi'])->whereIn('commission_tier', [2, 3])->sum('amount'),
                    'gross-adjustments' => (float)(clone $computations_ytd)->where('payroll_era', 'adjustments')->where('commission_type', 'adjustment-or')->sum('amount'),
                    'nett-adjustments' => (float)$ytd_cf['or_nett_adjustment'],
                ],
            ],
            // 'elite' => [
            //     'mtd' => (float)$mtd_cf['elite_mtd'],
            //     'ytd' => (float)(clone $computations_ytd)->where('payroll_era', 'adjustments')->where('commission_type', 'adjustment-elite')->sum('amount'),
            // ]
        ];
    }

    private static function payroll_commission_cf_helper($computations_mtd)
    {
        if ($computations_mtd->doesntExist()) return null;

        $basic_commission_mtd = (clone $computations_mtd)->whereIn('payroll_era', ['lfa','gi','adjustments'])->where('commission_tier', 1)->sum('amount');
        $basic_adjustment_mtd = (clone $computations_mtd)->where('payroll_era', 'adjustments')->where('commission_tier', 1)->sum('amount');
        $basic_cf_adjustment_mtd = ($basic_commission_mtd < 0) ? abs($basic_commission_mtd) : 0;
        $basic_nett_adjustment_mtd = bcadd($basic_adjustment_mtd, $basic_cf_adjustment_mtd, 10);
        $basic_nett_commission_mtd = bcadd($basic_commission_mtd, $basic_nett_adjustment_mtd, 10);

        $or_commission_mtd = (clone $computations_mtd)->whereIn('payroll_era', ['lfa','gi','adjustments'])->whereIn('commission_tier', [2,3])->sum('amount');
        $or_adjustment_mtd = (clone $computations_mtd)->where('payroll_era', 'adjustments')->whereIn('commission_tier', [2,3])->sum('amount');
        $or_cf_adjustment_mtd = ($or_commission_mtd < 0) ? abs($or_commission_mtd) : 0;
        $or_nett_adjustment_mtd = bcadd($or_adjustment_mtd, $or_cf_adjustment_mtd, 10);
        $or_nett_commission_mtd = bcadd($or_commission_mtd, $or_nett_adjustment_mtd, 10);

        $total_nett_adjustment_mtd = bcadd($basic_nett_adjustment_mtd, $or_nett_adjustment_mtd, 10);
        $basic_commission_aa_mtd = (clone $computations_mtd)->whereIn('payroll_era', ['aa'])->where('commission_tier', 1)->sum('amount');
        // $elite_mtd = (clone $computations_mtd)->where('payroll_era', 'adjustments')->where('commission_type', 'adjustment-elite')->sum('amount');
        // $remaining_commission_elite_aa = bcadd($basic_commission_aa_mtd, $elite_mtd, 10);
        $total_commission_basic_or = bcadd($basic_nett_commission_mtd, $or_nett_commission_mtd, 10);
        // $total_nett_commission_mtd = bcadd($total_commission_basic_or, $remaining_commission_elite_aa, 10);
        $total_nett_commission_mtd = bcadd($total_commission_basic_or, $basic_commission_aa_mtd, 10);

        return [
            'basic_commission_mtd' => (float)$basic_commission_mtd,
            'basic_adjustment_mtd' => (float)$basic_adjustment_mtd,
            'basic_cf_adjustment_mtd' => (float)$basic_cf_adjustment_mtd,
            'basic_nett_adjustment_mtd' => (float)$basic_nett_adjustment_mtd,
            'basic_nett_commission_mtd' => (float)$basic_nett_commission_mtd,
            'or_commission_mtd' => (float)$or_commission_mtd,
            'or_adjustment_mtd' => (float)$or_adjustment_mtd,
            'or_cf_adjustment_mtd' => (float)$or_cf_adjustment_mtd,
            'or_nett_adjustment_mtd' => (float)$or_nett_adjustment_mtd,
            'or_nett_commission_mtd' => (float)$or_nett_commission_mtd,
            'total_nett_adjustment_mtd' => (float)$total_nett_adjustment_mtd,
            'basic_commission_aa_mtd' => (float)$basic_commission_aa_mtd,
            // 'elite_mtd' => (float)$elite_mtd,
            // 'remaining_commission_elite_aa' => (float)$remaining_commission_elite_aa,
            'total_nett_commission_mtd' => (float)$total_nett_commission_mtd
        ];
    }




    /** ===================================================================================================
     * Functions to execute single payroll feed file
     *
     */
    public static function execute_feed($payroll_year, $payroll_month)
    {
        $payroll_date = Carbon::parse($payroll_year . '-' . $payroll_month);
        $batch = PayrollBatch::where('year', $payroll_year)->where('month', $payroll_month)->first();
        // $feed = self::generate_feed('adjustments', null, $payroll_year . '-' . $payroll_month . '---additionals.xlsx', 'adjustments');
        $feed = self::generate_feed('aa', 'aviva', $payroll_year . '-' . $payroll_month . '---aa-trailer.csv', 'trailer', 'trailer');
        // $feed = self::generate_feed('lfa', 'manulife', $payroll_year . '-' . $payroll_month . '---lfa-manulife-type-2.xlsx', 'life', 2);
        // $feed = self::generate_feed('lfa', 'aviva', $payroll_year . '-' . $payroll_month . '---lfa-aviva-ebh.xlsx', 'ebh');

        // Find out payroll type ...
        $payroll_provider = SelectProvider::firstAlias($feed['provider']);
        $payroll_types = (in_array($feed['era'], ['gi', 'adjustments'])) ? SelectPayrollFeedType::where('era', $feed['era']) : SelectPayrollFeedType::where('era', $feed['era'])->where('provider_slug', $payroll_provider->slug)->where('payroll_cat_slug', $feed['category']);
        $feed_type = $payroll_types->where('type', $feed['type'] ?? null)->first();

        // Remove prior records belonging to Feed Record ...
        PayrollFeed::where('batch_id', $batch->id)->where('filename', $feed['filename'])->delete();

        // Create New Feed Record (if doesnt exists) ...
        $feed_record = PayrollFeed::updateOrCreate([
            'batch_id' => $batch->id,
            'year' => $batch->year,
            'month' => $batch->month,
            'filename' => $feed['filename'],
            'provider_slug' => $payroll_provider->slug ?? null,
            'payroll_type_slug' => $feed_type['slug'],
            'gst_rate' => $feed['gst_rate'] ?? 0.00,
            'csv_pipe' => $feed['csv_pipe'] ?? false,
        ], [
            'date_received' => $feed['date_received'] ?? null,
            'processed' => false
        ]);

        // Dispatch job for verifying feed
        PayrollFeedProcessor::dispatch($feed_record)->onQueue('Payroll-Feeds');
        return 'Pushed ' . $feed['filename'] . ' onto the queue: Payroll-Feeds';
    }


    /** ===================================================================================================
     * Functions to execute monthly payroll computations
     *
     */
    public static function execute_computations($year, $month)
    {
        $batch = PayrollBatch::where('year', $year)->where('month', $month)->first();
        $computations = PayrollComputation::where('batch_id', $batch->id)->get();
        $date_export = Carbon::parse($year.'-'.$month.'-01');
        $filename = 'storage/app/payroll/computations/'.Carbon::now()->format('Ymd').time().'---'.$year.'-'.  $date_export->format("F") . '.xlsx';

        if ($computations->count()) {
            (new FastExcel($computations))->export($filename, function ($data) {
                $date_inception = Carbon::parse($data->record->date_inception);

                return [
                    'Payroll Year' => $data->year,
                    'Payroll Month' => $data->month,
                    'Payroll Era' => $data->payroll_era,
                    'Provider Name' => $data->provider_name,
                    'Category' => $data->category_name,
                    'Policy Number' => $data->policy->policy_no ?? '',
                    'Policy Holder' => $data->client->name ?? '',
                    'Closed By (Code)' => ($data->closed_by) ? ($data->closed_by->lfa_code ?? $data->closed_by->lfa_sl_no) : '-',
                    'Closed By (Name)' => ($data->closed_by) ? $data->closed_by->name : 'Legacy FA Pte Ltd',
                    'Commission Tier' => $data->commission_tier,
                    'associate (Code)' => ($data->associate) ? ($data->associate->lfa_code ?? $data->associate->lfa_sl_no) : '-',
                    'associate (Name)' => ($data->associate) ? $data->associate->name : 'Legacy FA Pte Ltd',
                    'Payee (Code)' => ($data->payee) ? ($data->payee->lfa_code ?? $data->payee->lfa_sl_no) : '-',
                    'Payee (Name)' => ($data->payee) ? $data->payee->name : 'Legacy FA Pte Ltd',
                    'Commission' => $data->amount,
                    'Commission Type' => $data->commission_type,
                    'Date Inception' => $date_inception->format('d/m/Y') ?? '-',
                    'Firm Revenue' => ($data->firm_revenue) ? 'Yes' : '-',
                    'Remarks' => $data->remarks
                ];
            });
        }
    }

}