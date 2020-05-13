<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSelections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** ===================================================================================================
         * Default Banks
         */
        Schema::connection('lfa_selections')->create('banks', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('full_name');
            $table->string('alias')->nullable();
            $table->text('address')->nullable();
            $table->string('contact')->nullable();
            $table->boolean('activated')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Default Countries, Currencies, Languages
         */
        Schema::connection('lfa_selections')->create('countries', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('nativeName')->nullable();;
            $table->string('numericCode')->nullable();
            $table->string('alpha2Code')->nullable();
            $table->string('alpha3Code')->nullable();
            $table->string('capital')->nullable();
            $table->string('nationality')->nullable();
            $table->string('region')->nullable();
            $table->string('subregion')->nullable();
            $table->string('flag')->nullable();
        });
        Schema::connection('lfa_selections')->create('currencies', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('code');
            $table->string('symbol')->nullable();
        });
        Schema::connection('lfa_selections')->create('languages', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('nativeName')->nullable();
            $table->string('iso639_1')->nullable();
            $table->string('iso639_2')->nullable();
        });
        Schema::connection('lfa_selections')->create('countries_has_languages', function (Blueprint $table) {
            $table->unsignedInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->unsignedInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->primary(['country_id', 'language_id']);
        });
        Schema::connection('lfa_selections')->create('countries_has_currencies', function (Blueprint $table) {
            $table->unsignedInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->unsignedInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->primary(['country_id', 'currency_id']);
        });

        /** ===================================================================================================
         * Default Individual(s) -  Salutations
         */
        Schema::connection('lfa_selections')->create('salutations', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) -  Genders
         */
        Schema::connection('lfa_selections')->create('genders', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) -  Race
         */
        Schema::connection('lfa_selections')->create('race', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) -  Marital Status
         */
        Schema::connection('lfa_selections')->create('marital_status', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) -  Residency Status
         */
        Schema::connection('lfa_selections')->create('residency_status', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) -  Employment Status
         */
        Schema::connection('lfa_selections')->create('employment_status', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Educational Levels
         */
        Schema::connection('lfa_selections')->create('educational_levels', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) - Address Types
         */
        Schema::connection('lfa_selections')->create('address_types', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) -  Contact Types
         */
        Schema::connection('lfa_selections')->create('contact_types', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default Individual(s) - Relationship Types
         */
        Schema::connection('lfa_selections')->create('relationship_types', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });


        /** ===================================================================================================
         * Default AA/LFA -- for AA inception date replacements
         * _aa_policy_dates
         */
        Schema::connection('lfa_selections')->create('_aa_policy_dates', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('policy_number');
            $table->string('agent_code');
            $table->string('type')->nullable();
            $table->date('old_date')->nullable();
            $table->date('new_date')->nullable();
        });

        /** ===================================================================================================
         * Default LFA Providers
         */
        Schema::connection('lfa_selections')->create('_lfa_providers', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('full_name');
            $table->string('alias')->nullable();
            $table->string('color')->nullable();
            $table->string('background')->nullable();
            $table->integer('code_length')->nullable();
            $table->boolean('activated')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Default LFA Associates - Onboarding Status
         */
        Schema::connection('lfa_selections')->create('_lfa_onboarding_status', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Associates - Designations
         */
        Schema::connection('lfa_selections')->create('_lfa_designations', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('salesforce')->default(false);
            $table->unsignedInteger('salesforce_tier')->nullable()->default(null);
            $table->boolean('override')->default(false);
            $table->unsignedInteger('manager_or_self')->nullable()->default(null);
            $table->unsignedInteger('manager_or_agent')->nullable()->default(null);
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Associates - RNF Status
         */
        Schema::connection('lfa_selections')->create('_lfa_associates_rnf_status', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Associates - Allowance Schemes
         */
        Schema::connection('lfa_selections')->create('_lfa_associates_allowance_schemes', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Associates - Team(s) - Types
         */
        Schema::connection('lfa_selections')->create('_lfa_associates_teams_types', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Associates - Team(s) - Roles
         */
        Schema::connection('lfa_selections')->create('_lfa_associates_teams_roles', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Client(s) - Types
         */
        Schema::connection('lfa_selections')->create('_lfa_client_types', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Client(s) - Sources
         */
        Schema::connection('lfa_selections')->create('_lfa_client_sources', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Client(s) - Introducer(s) - Nominee(s) - Status
         */
        Schema::connection('lfa_selections')->create('_lfa_nominee_status', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Client(s) - Introducer(s) - Nominee(s) - Status
         */
        Schema::connection('lfa_selections')->create('_lfa_nominee_benefits', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Payroll - Categories
         */
        Schema::connection('lfa_selections')->create('_lfa_payroll_categories', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Payroll - Commission Feeds - File Mapping
         */
        Schema::connection('lfa_selections')->create('_lfa_payroll_comm_feed_mapping', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('agent_no', 50)->nullable();
            $table->string('policy_holder_name', 50)->nullable();
            $table->string('policy_holder_nric', 50)->nullable();
            $table->string('life_assured_name', 50)->nullable();
            $table->string('life_assured_nric', 50)->nullable();
            $table->string('policy_no', 50)->nullable();
            $table->string('product_code', 50)->nullable();
            $table->string('product_type', 50)->nullable();
            $table->string('product_name', 50)->nullable();
            $table->string('component_code', 50)->nullable();
            $table->string('component_name', 50)->nullable();
            $table->string('contract_currency', 50)->nullable();
            $table->string('policy_term', 50)->nullable();
            $table->string('premium_term', 50)->nullable();
            $table->string('payment_frequency', 50)->nullable();
            $table->string('date_issued', 50)->nullable();
            $table->string('date_inception', 50)->nullable();
            $table->string('date_expiry', 50)->nullable();
            $table->string('payment_currency', 50)->nullable();
            $table->string('sum_assured', 50)->nullable();
            $table->string('total_investment', 50)->nullable();
            $table->string('premium', 50)->nullable();
            $table->string('premium_gst', 50)->nullable();
            $table->string('premium_loading', 50)->nullable();
            $table->string('premium_conversion_rate', 50)->nullable();
            $table->string('premium_type', 50)->nullable();
            $table->string('transaction_no', 50)->nullable();
            $table->string('transaction_code', 50)->nullable();
            $table->string('transaction_desc', 50)->nullable();
            $table->string('date_transaction', 50)->nullable();
            $table->string('date_instalment_from', 50)->nullable();
            $table->string('date_instalment_to', 50)->nullable();
            $table->string('date_due', 50)->nullable();
            $table->string('commission_type', 50)->nullable();
            $table->string('commission_currency', 50)->nullable();
            $table->string('commission', 50)->nullable();
            $table->string('commission_gst', 50)->nullable();
            $table->string('commission_conversion_rate', 50)->nullable();
            $table->string('date_commission', 50)->nullable();
        });

        /** ===================================================================================================
         * Default LFA Payroll - Commission Feeds - Types
         */
        Schema::connection('lfa_selections')->create('_lfa_payroll_comm_feed_types', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title')->nullable();
            $table->string('era')->nullable();
            $table->string('type')->nullable();
            $table->string('date_format')->nullable();
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('_lfa_providers')->onUpdate('cascade');
            $table->string('payroll_cat_slug')->nullable();
            $table->foreign('payroll_cat_slug')->references('slug')->on('_lfa_payroll_categories')->onUpdate('cascade');
            $table->unsignedInteger('payroll_mapping_id')->nullable();
            $table->foreign('payroll_mapping_id')->references('id')->on('_lfa_payroll_comm_feed_mapping');
            $table->boolean('gst_included')->default(0);
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Products - Categories
         */
        Schema::connection('lfa_selections')->create('_lfa_product_categories', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('permission')->nullable();
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Products - Coverage
         */
        Schema::connection('lfa_selections')->create('_lfa_product_coverage', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('product_cat_slug')->nullable();
            $table->foreign('product_cat_slug')->references('slug')->on('_lfa_product_categories')->onUpdate('cascade');
            $table->string('title');
            $table->string('type');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Submissions - Categories
         * Insurance, Investments, Bank Loans (Referral), Wills (Referral)
         */
        Schema::connection('lfa_selections')->create('_lfa_submission_categories', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Submissions - Status
         * 0 - Rejected (Pending Rep/User Amendments)
         * 1 - Draft (Pending Rep/User Submission)
         * 2 - Submitted (Pending Supervisor Approval)
         * 3 - Approved (Pending Admin Verification)
         * 4 - Verified (Pending Official Submission)
         * 5 - Submitted to Provider
         */
        Schema::connection('lfa_selections')->create('_lfa_submission_status', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->tinyInteger('step')->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('description')->nullable();
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Selections - Payment Modes
         */
        Schema::connection('lfa_selections')->create('_lfa_payment_modes', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Selections - Sales Activities
         */
        Schema::connection('lfa_selections')->create('_lfa_sales_activities', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Selections - Sales Activities
         */
        Schema::connection('lfa_selections')->create('_lfa_sales_stage', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->tinyInteger('step')->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Selections - Lead Stages
         */
        Schema::connection('lfa_selections')->create('_lfa_lead_stage', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->tinyInteger('step')->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });

        /** ===================================================================================================
         * Default LFA Selections - Outcomes
         */
        Schema::connection('lfa_selections')->create('_lfa_outcomes', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('slug')->unique();
            $table->string('title');
            $table->boolean('activated')->default(1);
        });



        /** ===================================================================================================
         * Main Submissions - Submitted Introducer Cases
         */
        Schema::connection('lfa_selections')->create('_lfa_providers_has_submission_cat', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('_lfa_providers')->onUpdate('cascade');
            $table->string('submission_cat_slug')->nullable();
            $table->foreign('submission_cat_slug')->references('slug')->on('_lfa_submission_categories')->onUpdate('cascade');
            $table->unique(['provider_slug', 'submission_cat_slug'], 'unique_provider_sub');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_selections')->disableForeignKeyConstraints();
        Schema::connection('lfa_selections')->dropIfExists('_lfa_providers_has_submission_cat');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_outcomes');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_lead_stage');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_sales_stage');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_sales_activities');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_payment_modes');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_submission_status');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_submission_categories');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_product_coverage');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_product_categories');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_payroll_comm_feed_types');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_payroll_comm_feed_mapping');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_payroll_categories');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_nominee_benefits');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_nominee_status');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_client_sources');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_client_types');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_associates_teams_roles');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_associates_teams_types');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_associates_allowance_schemes');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_associates_rnf_status');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_designations');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_onboarding_status');
        Schema::connection('lfa_selections')->dropIfExists('_lfa_providers');
        Schema::connection('lfa_selections')->dropIfExists('_aa_policy_dates');
        Schema::connection('lfa_selections')->dropIfExists('relationship_types');
        Schema::connection('lfa_selections')->dropIfExists('address_types');
        Schema::connection('lfa_selections')->dropIfExists('contact_types');
        Schema::connection('lfa_selections')->dropIfExists('educational_levels');
        Schema::connection('lfa_selections')->dropIfExists('employment_status');
        Schema::connection('lfa_selections')->dropIfExists('residency_status');
        Schema::connection('lfa_selections')->dropIfExists('marital_status');
        Schema::connection('lfa_selections')->dropIfExists('race');
        Schema::connection('lfa_selections')->dropIfExists('genders');
        Schema::connection('lfa_selections')->dropIfExists('salutations');
        Schema::connection('lfa_selections')->dropIfExists('countries_has_currencies');
        Schema::connection('lfa_selections')->dropIfExists('countries_has_languages');
        Schema::connection('lfa_selections')->dropIfExists('languages');
        Schema::connection('lfa_selections')->dropIfExists('currencies');
        Schema::connection('lfa_selections')->dropIfExists('countries');
        Schema::connection('lfa_selections')->dropIfExists('banks');
        Schema::connection('lfa_selections')->enableForeignKeyConstraints();
    }
}
