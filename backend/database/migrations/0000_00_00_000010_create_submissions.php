<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubmissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** ===================================================================================================
         * Main Submissions
         */
        Schema::connection('lfa_submissions')->create('submissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->string('status_slug')->nullable();
            $table->foreign('status_slug')->references('slug')->on('lfa_selections._lfa_submission_status')->onUpdate('cascade');
            // Representative
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // Policy Holder
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('set null');
            // Portal DB Reference
            $table->unsignedInteger('reference_pid')->nullable();
            $table->date('date_submission')->nullable();
            // Snapshot of Submitted Details
            $table->string('client_type')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_business_name')->nullable();
            $table->string('client_business_uen')->nullable();
            $table->text('client_description')->nullable();
            $table->string('associate_name')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->json('client_personal')->nullable();
            $table->json('client_business')->nullable();
            //
            $table->text('remarks')->nullable();
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Cases/Product(s) tagged to each Submission
         */
        Schema::connection('lfa_submissions')->create('cases', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Submission Group
            $table->uuid('submission_uuid');
            $table->foreign('submission_uuid')->references('uuid')->on('submissions')->onDelete('cascade');
            // Representative
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // Policy Holder
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('set null');
            // Life Assured
            $table->uuid('life_assured_uuid')->nullable();
            $table->foreign('life_assured_uuid')->references('uuid')->on('lfa_clients.life_assured')->onDelete('set null');
            // Provider
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            // Case Information
            $table->string('submission_cat_slug')->nullable();
            $table->foreign('submission_cat_slug')->references('slug')->on('lfa_selections._lfa_submission_categories')->onUpdate('cascade');
            $table->string('product_cat_slug')->nullable();
            $table->foreign('product_cat_slug')->references('slug')->on('lfa_selections._lfa_product_categories')->onUpdate('cascade');
            $table->uuid('product_uuid')->nullable();
            $table->foreign('product_uuid')->references('uuid')->on('lfa_products.products')->onDelete('set null');
            $table->uuid('product_option_uuid')->nullable();
            $table->foreign('product_option_uuid')->references('uuid')->on('lfa_products.product_options')->onDelete('set null');
            $table->string('payment_mode_slug')->nullable();
            $table->foreign('payment_mode_slug')->references('slug')->on('lfa_selections._lfa_payment_modes')->onUpdate('cascade');

            // Case information (Investment)
            $table->string('investment_transaction_type')->nullable();
            $table->string('investment_account_type')->nullable();
            // Case information (Loans - Referrals)
            $table->string('loan_platform')->nullable(); // icompareloan or collab-ventures or red-brick
            $table->string('loan_property_address')->nullable();
            $table->string('loan_consent')->nullable();
            $table->double('loan_amount', 15, 5)->default(0.00);
            $table->double('loan_interest_rate', 15, 5)->default(0.00);
            $table->string('loan_bank_slug')->nullable();

            // Snapshot - Case Information (Common Denominator)
            $table->char('currency', 3)->default('SGD');
            $table->double('ape', 15, 5)->default(0.00);
            $table->string('life_assured_name')->nullable();
            $table->boolean('life_assured_is_client')->default(false);
            $table->string('life_assured_relationship')->nullable();
            $table->json('life_assured_personal')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('submission_category')->nullable();
            $table->string('product_category')->nullable();
            $table->string('product_name')->nullable();
            $table->string('option_name')->nullable();

            $table->double('sum_assured', 15, 5)->default(0.00);
            $table->unsignedInteger('policy_term')->default(0);
            $table->unsignedInteger('payment_term')->default(0);
            $table->string('payment_frequency')->nullable();
            $table->string('payment_type')->nullable(); // regular or single
            $table->double('gst_rate', 15, 5)->default(0.00);
            $table->double('gross_payment_before_gst', 15, 5)->default(0.00);
            $table->double('gross_payment_gst', 15, 5)->default(0.00);
            $table->double('gross_payment_after_gst', 15, 5)->default(0.00);
            $table->double('payment_discount', 15, 5)->default(0.00);
            $table->double('nett_payment_before_gst', 15, 5)->default(0.00);
            $table->double('nett_payment_gst', 15, 5)->default(0.00);
            $table->double('nett_payment_after_gst', 15, 5)->default(0.00);
            $table->string('payment_mode')->nullable();

            // Footnotes
            $table->string('submission_mode')->nullable();
            $table->string('reference_no')->nullable();
            $table->timestamps();
        });

        /** ===================================================================================================
         * Detail Information on each Case
         */
        Schema::connection('lfa_submissions')->create('case_info', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            // Submission Group
            $table->uuid('case_uuid');
            $table->foreign('case_uuid')->references('uuid')->on('cases')->onDelete('cascade');
            // Case information
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        /** ===================================================================================================
         * Cases/Product(s) tagged to each Submission
         */
        Schema::connection('lfa_submissions')->create('case_riders', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('case_uuid');
            $table->foreign('case_uuid')->references('uuid')->on('cases')->onDelete('cascade');
            // Case information (Insurance)
            $table->uuid('rider_uuid')->nullable();
            $table->foreign('rider_uuid')->references('uuid')->on('lfa_products.riders')->onDelete('set null');
            $table->string('rider_name')->nullable();
            //
            $table->double('sum_assured', 15, 5)->default(0.00);
            $table->unsignedInteger('policy_term')->default(0);
            $table->unsignedInteger('payment_term')->default(0);
            //
            $table->double('gst_rate', 15, 5)->default(0.00);
            $table->double('gross_payment_before_gst', 15, 5)->default(0.00);
            $table->double('gross_payment_gst', 15, 5)->default(0.00);
            $table->double('gross_payment_after_gst', 15, 5)->default(0.00);
            $table->double('payment_discount', 15, 5)->default(0.00);
            $table->double('nett_payment_before_gst', 15, 5)->default(0.00);
            $table->double('nett_payment_gst', 15, 5)->default(0.00);
            $table->double('nett_payment_after_gst', 15, 5)->default(0.00);
            $table->timestamps();
        });

        /** ===================================================================================================
         * Main Submissions - Submitted Introducer Cases
         * Data will be populated either automatially when submission status is submitted (locked)
         * Or when admin manually populate it to be on this table (for tracking purpose).
         */
        Schema::connection('lfa_submissions')->create('introducer_cases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('submission_uuid');
            $table->foreign('submission_uuid')->references('uuid')->on('submissions')->onDelete('cascade');
            $table->uuid('case_uuid');
            $table->foreign('case_uuid')->references('uuid')->on('cases')->onDelete('cascade');
            // Representative
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // Policy Holder
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('set null');
            // Life Assured
            $table->boolean('life_assured_is_client')->default(false);
            $table->uuid('life_assured_uuid')->nullable();
            $table->foreign('life_assured_uuid')->references('uuid')->on('lfa_clients.life_assured')->onDelete('set null');
            // Introducer
            $table->uuid('introducer_uuid')->nullable();
            $table->foreign('introducer_uuid')->references('uuid')->on('lfa_clients.introducers')->onDelete('set null');
            // Nominee
            $table->uuid('nominee_uuid')->nullable();
            $table->foreign('nominee_uuid')->references('uuid')->on('lfa_clients.nominees')->onDelete('set null');
            //
            $table->string('introducer_scheme_type')->default('introducer'); // introducer or nominee
            // Snapshot details
            $table->string('introducer_name')->nullable();
            $table->string('nominee_name')->nullable();
            $table->string('associate_name')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_nric_no')->nullable();
            $table->string('life_assured_name')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('submission_category')->nullable();
            $table->string('product_category')->nullable();
            $table->string('product_name')->nullable();
            $table->string('option_name')->nullable();
            $table->double('sum_assured', 15, 5)->default(0.00);
            $table->unsignedInteger('policy_term')->default(0);
            $table->unsignedInteger('payment_term')->default(0);
            $table->string('payment_frequency')->nullable();
            $table->string('payment_type')->default('regular'); // regular or single
            // $table->double('payment', 15, 5)->default(0.00);
            // $table->double('payment_gst', 15, 5)->default(0.00);
            $table->double('gst_rate', 15, 5)->default(0.00);
            $table->double('gross_payment_before_gst', 15, 5)->default(0.00);
            $table->double('gross_payment_gst', 15, 5)->default(0.00);
            $table->double('gross_payment_after_gst', 15, 5)->default(0.00);
            $table->double('payment_discount', 15, 5)->default(0.00);
            $table->double('nett_payment_before_gst', 15, 5)->default(0.00);
            $table->double('nett_payment_gst', 15, 5)->default(0.00);
            $table->double('nett_payment_after_gst', 15, 5)->default(0.00);
            $table->string('payment_mode')->nullable();
            $table->char('currency', 3)->default('SGD');
            $table->double('ape', 15, 5)->default(0.00);
            // Receiver details
            $table->boolean('scheme_paid')->default(0);
            $table->string('scheme_receiver_type')->nullable();  // introducer or nominee or charity
            $table->string('scheme_receiver_name')->nullable();
            $table->string('scheme_bank_name')->nullable();
            $table->string('scheme_bank_account_no')->nullable();
            $table->string('scheme_bank_reference_no')->nullable();
            $table->double('scheme_amount', 15, 5)->default(0.00);
            $table->date('scheme_date_payment')->nullable();
            $table->string('scheme_payment_mode_slug')->nullable();
            $table->foreign('scheme_payment_mode_slug')->references('slug')->on('lfa_selections._lfa_payment_modes')->onUpdate('cascade');
            $table->string('scheme_payment_mode')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Main Submissions - Submitted Introducer Cases
         * Data will be populated either automatially when submission status is submitted (locked)
         * Or when admin manually populate it to be on this table (for tracking purpose).
         */
        Schema::connection('lfa_submissions')->create('introducer_cases_has_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('introducer_case_uuid');
            $table->foreign('introducer_case_uuid')->references('uuid')->on('introducer_cases')->onDelete('cascade');
            $table->uuid('policy_uuid')->nullable();
            $table->foreign('policy_uuid')->references('uuid')->on('lfa_policies.policies')->onDelete('set null');
            $table->unique(['introducer_case_uuid', 'policy_uuid'], 'introducer_cases_has_unique_policies');
            $table->double('gross_revenue', 15, 5)->default(0.00);
            $table->double('rep_base_commission', 15, 5)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_submissions')->disableForeignKeyConstraints();
        Schema::connection('lfa_submissions')->dropIfExists('introducer_cases_has_policies');
        Schema::connection('lfa_submissions')->dropIfExists('introducer_cases');
        Schema::connection('lfa_submissions')->dropIfExists('case_riders');
        Schema::connection('lfa_submissions')->dropIfExists('case_info');
        Schema::connection('lfa_submissions')->dropIfExists('cases');
        Schema::connection('lfa_submissions')->dropIfExists('submissions');
        Schema::connection('lfa_submissions')->enableForeignKeyConstraints();
    }
}
