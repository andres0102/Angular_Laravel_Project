<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayroll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('lfa_payroll')->create('batches', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('year', 4)->nullable();
            $table->string('month', 2)->nullable();
            $table->boolean('release')->default(false);
            $table->timestamps();
        });

        Schema::connection('lfa_payroll')->create('feeds', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            // Batch/Feed information
            $table->string('year', 4)->nullable();
            $table->string('month', 2)->nullable();
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->string('payroll_type_slug')->nullable();
            $table->foreign('payroll_type_slug')->references('slug')->on('lfa_selections._lfa_payroll_comm_feed_types')->onUpdate('cascade');
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            // Feed information
            $table->string('filename')->nullable();
            $table->boolean('csv_pipe')->default(false);
            $table->date('date_received')->nullable();
            $table->double('total_feed_deposits', 15, 5)->default(0.00);
            $table->double('import_commission', 15, 5)->default(0.00);
            $table->double('gst_rate', 15, 5)->default(0.00);
            $table->boolean('processed')->default(false);
            $table->timestamps();
        });

        Schema::connection('lfa_payroll')->create('records', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            // Batch/Feed information
            $table->string('year', 4)->nullable();
            $table->string('month', 2)->nullable();
            $table->unsignedBigInteger('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->string('payroll_type_slug')->nullable();
            $table->foreign('payroll_type_slug')->references('slug')->on('lfa_selections._lfa_payroll_comm_feed_types')->onUpdate('cascade');
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            // Feed/Record information
            $table->unsignedBigInteger('feed_id');
            $table->foreign('feed_id')->references('id')->on('feeds')->onDelete('cascade');
            $table->string('payroll_era', 50)->nullable();
            $table->string('payroll_cat_slug')->nullable();
            $table->foreign('payroll_cat_slug')->references('slug')->on('lfa_selections._lfa_payroll_categories')->onUpdate('cascade');
            // Foreign model references
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('set null');
            $table->uuid('life_assured_uuid')->nullable();
            $table->foreign('life_assured_uuid')->references('uuid')->on('lfa_clients.life_assured')->onDelete('set null');
            $table->uuid('policy_uuid')->nullable();
            $table->foreign('policy_uuid')->references('uuid')->on('lfa_policies.policies')->onDelete('set null');
            $table->uuid('policy_transaction_uuid')->nullable();
            $table->foreign('policy_transaction_uuid')->references('uuid')->on('lfa_policies.transactions')->onDelete('set null');
            // Boolean values
            $table->boolean('processed')->default(false);
            $table->boolean('validated')->default(false);
            $table->boolean('verified_agent_code')->default(false);
            $table->boolean('firm_revenue')->default(false);
            // Fixed Policy Record information
            $table->string('agent_no', 30)->nullable();
            $table->string('policy_holder_name')->nullable();
            $table->string('policy_holder_nric', 30)->nullable();
            $table->string('life_assured_name')->nullable();
            $table->string('life_assured_nric', 30)->nullable();
            $table->string('policy_no')->nullable();
            $table->string('product_code', 30)->nullable();
            $table->string('product_type', 30)->nullable();
            $table->string('product_name')->nullable();
            $table->string('component_code', 30)->nullable();
            $table->string('component_name')->nullable();
            $table->char('contract_currency', 3)->default('SGD');
            $table->double('sum_insured', 15, 5)->default(0.00);
            $table->double('sum_assured', 15, 5)->default(0.00);
            $table->tinyInteger('policy_term')->default(0);
            $table->tinyInteger('premium_term')->default(0);
            $table->string('payment_frequency', 20)->nullable();
            $table->date('date_issued')->nullable();
            $table->date('date_inception')->nullable();
            $table->date('date_expiry')->nullable();
            $table->char('payment_currency', 3)->default('SGD');
            $table->double('total_investment', 15, 5)->default(0.00);
            $table->double('premium', 15, 5)->default(0.00);
            $table->double('premium_gst', 15, 5)->default(0.00);
            $table->double('premium_loading', 15, 5)->default(0.00);
            $table->double('premium_conversion_rate', 15, 5)->default(1.00);
            $table->string('premium_type', 30)->default('regular');
            // Transaction information
            $table->string('transaction_no', 30)->nullable();
            $table->string('transaction_code', 30)->nullable();
            $table->text('transaction_desc')->nullable();
            $table->date('date_transaction')->nullable();
            $table->date('date_instalment_from')->nullable();
            $table->date('date_instalment_to')->nullable();
            $table->date('date_due')->nullable();
            $table->string('commission_type')->default('renewal');
            $table->char('commission_currency', 3)->default('SGD');
            $table->double('commission', 15, 5)->default(0.00);
            $table->double('commission_gst', 15, 5)->default(0.00);
            $table->double('commission_conversion_rate', 15, 5)->default(1.00);
            $table->date('date_commission')->nullable();
            $table->timestamps();
        });

        // Table to store :: Adjustments / Elite Scheme / Incentives / etc..
        Schema::connection('lfa_payroll')->create('adjustments', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            // Batch/Feed information
            $table->string('year', 4)->nullable();
            $table->string('month', 2)->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->string('payroll_type_slug')->nullable();
            $table->foreign('payroll_type_slug')->references('slug')->on('lfa_selections._lfa_payroll_comm_feed_types')->onUpdate('cascade');
            // Feed/Record information
            $table->unsignedBigInteger('feed_id')->nullable();
            $table->foreign('feed_id')->references('id')->on('feeds')->onDelete('cascade');
            $table->string('payroll_cat_slug')->nullable();
            $table->foreign('payroll_cat_slug')->references('slug')->on('lfa_selections._lfa_payroll_categories')->onUpdate('cascade');
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            // Foreign model references
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('set null');
            // Boolean values
            $table->boolean('processed')->default(false);
            // Data
            $table->text('description')->nullable();
            $table->string('tier')->default('basic');
            $table->double('amount', 15, 5)->default(0.00);
            $table->date('date_transaction')->nullable();
            $table->timestamps();
        });

        Schema::connection('lfa_payroll')->create('instructions', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('era')->nullable();
            $table->unsignedInteger('commission_tier')->default(1);
            $table->uuid('from_associate_uuid')->nullable();
            $table->foreign('from_associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->uuid('to_associate_uuid')->nullable();
            $table->foreign('to_associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->timestamps();
        });

        Schema::connection('lfa_payroll')->create('computations', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            // Batch/Feed information
            $table->string('year', 4)->nullable();
            $table->string('month', 2)->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
            $table->string('payroll_type_slug')->nullable();
            $table->foreign('payroll_type_slug')->references('slug')->on('lfa_selections._lfa_payroll_comm_feed_types')->onUpdate('cascade');
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            // Feed information
            $table->unsignedBigInteger('feed_id')->nullable();
            $table->foreign('feed_id')->references('id')->on('feeds')->onDelete('cascade');
            $table->string('payroll_era', 50)->nullable();
            $table->string('payroll_cat_slug')->nullable();
            $table->foreign('payroll_cat_slug')->references('slug')->on('lfa_selections._lfa_payroll_categories')->onUpdate('cascade');
            // Record Information
            $table->string('record_type')->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            // $table->foreign('record_id')->references('id')->on('records')->onDelete('cascade');
            // Payroll information
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('set null');
            $table->uuid('policy_uuid')->nullable();
            $table->foreign('policy_uuid')->references('uuid')->on('lfa_policies.policies')->onDelete('set null');
            $table->uuid('policy_transaction_uuid')->nullable();
            $table->foreign('policy_transaction_uuid')->references('uuid')->on('lfa_policies.transactions')->onDelete('set null');
            // -- Policy is closed by ::
            $table->uuid('closed_by_uuid')->nullable();
            $table->foreign('closed_by_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // -- Policy commission's associate ::
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // -- Policy payment is paid/redirected to ::
            $table->uuid('payee_uuid')->nullable();
            $table->foreign('payee_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // Variables
            $table->unsignedInteger('commission_tier')->default(1);
            $table->double('amount', 15, 5)->default(0.00);
            $table->boolean('firm_revenue')->default(false);
            $table->string('commission_type');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::connection('lfa_payroll')->create('firm_codes', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            $table->string('code');
            $table->timestamps();
        });

        Schema::connection('lfa_policies')->table('transactions', function (Blueprint $table) {
            $table->foreign('payroll_batch_id')->references('id')->on('lfa_payroll.batches')->onDelete('cascade');
            $table->foreign('payroll_feed_id')->references('id')->on('lfa_payroll.feeds')->onDelete('cascade');
            $table->foreign('payroll_record_id')->references('id')->on('lfa_payroll.records')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_payroll')->disableForeignKeyConstraints();
        Schema::connection('lfa_payroll')->dropIfExists('firm_codes');
        Schema::connection('lfa_payroll')->dropIfExists('computations');
        Schema::connection('lfa_payroll')->dropIfExists('instructions');
        Schema::connection('lfa_payroll')->dropIfExists('adjustments');
        Schema::connection('lfa_payroll')->dropIfExists('records');
        Schema::connection('lfa_payroll')->dropIfExists('feeds');
        Schema::connection('lfa_payroll')->dropIfExists('batches');
        Schema::connection('lfa_payroll')->enableForeignKeyConstraints();
    }
}
