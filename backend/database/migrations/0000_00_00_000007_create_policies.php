<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePolicies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('lfa_policies')->create('policies', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            $table->string('policy_no');
            // Policy Holder
            $table->uuid('client_uuid');
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('cascade');
            $table->unique(['associate_uuid', 'client_uuid', 'provider_slug', 'policy_no'], 'unique_policy');
            // Life Assured
            $table->uuid('life_assured_uuid')->nullable();
            $table->foreign('life_assured_uuid')->references('uuid')->on('lfa_clients.life_assured')->onDelete('set null');
            // Policy Information
            $table->string('policy_holder_name')->nullable();
            $table->string('policy_holder_nric', 30)->nullable();
            $table->string('life_assured_name')->nullable();
            $table->string('life_assured_nric', 30)->nullable();
            $table->char('contract_currency', 3)->default('SGD');
            $table->unsignedInteger('policy_term')->default(0);
            $table->unsignedInteger('premium_term')->default(0);
            $table->string('payment_frequency', 20)->nullable();
            $table->date('date_issued')->nullable();
            $table->date('date_inception')->nullable();
            $table->date('date_expiry')->nullable();
            $table->double('sum_assured', 15, 5)->default(0.00);
            $table->double('total_investment', 15, 5)->default(0.00);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('lfa_policies')->create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Policy Information
            $table->uuid('policy_uuid');
            $table->foreign('policy_uuid')->references('uuid')->on('lfa_policies.policies')->onDelete('cascade');
            // Payroll Information
            $table->string('year', 4)->nullable();
            $table->string('month', 2)->nullable();
            $table->unsignedBigInteger('payroll_batch_id')->nullable();     // Add foreign key reference after table is created
            $table->unsignedBigInteger('payroll_feed_id')->nullable();      // Add foreign key reference after table is created
            $table->unsignedBigInteger('payroll_record_id')->nullable();    // Add foreign key reference after table is created
            // Transaction information
            $table->string('transaction_no', 30)->nullable();
            $table->string('transaction_code', 30)->nullable();
            $table->text('transaction_desc')->nullable();
            $table->date('date_transaction')->nullable();
            $table->date('date_instalment_from')->nullable();
            $table->date('date_instalment_to')->nullable();
            $table->date('date_due')->nullable();
            // Product information
            $table->string('product_code', 30)->nullable();
            $table->string('product_type', 30)->nullable();
            $table->string('product_name')->nullable();
            $table->string('component_code', 30)->nullable();
            $table->string('component_name')->nullable();
            // Premiums/Investment Information
            $table->char('payment_currency', 3)->default('SGD');
            $table->double('premium', 15, 5)->default(0.00);
            $table->double('premium_gst', 15, 5)->default(0.00);
            $table->double('premium_loading', 15, 5)->default(0.00);
            $table->double('premium_conversion_rate', 15, 5)->default(1.00);
            $table->string('premium_type', 30)->default('regular');
            // Commission information
            $table->string('commission_type', 30)->default('renewal');
            $table->char('commission_currency', 3)->default('SGD');
            $table->double('commission', 15, 5)->default(0.00);
            $table->double('commission_gst', 15, 5)->default(0.00);
            $table->double('commission_conversion_rate', 15, 5)->default(1.00);
            $table->date('date_commission')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_policies')->disableForeignKeyConstraints();
        Schema::connection('lfa_policies')->dropIfExists('transactions');
        Schema::connection('lfa_policies')->dropIfExists('policies');
        Schema::connection('lfa_policies')->enableForeignKeyConstraints();
    }
}
