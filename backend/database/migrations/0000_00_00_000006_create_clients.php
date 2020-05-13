<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('lfa_clients')->create('clients', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->string('client_type_slug')->nullable();
            $table->foreign('client_type_slug')->references('slug')->on('lfa_selections._lfa_client_types')->onUpdate('cascade');
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->uuid('individual_uuid')->nullable();
            $table->foreign('individual_uuid')->references('uuid')->on('lfa_individuals.individuals')->onDelete('cascade');

            $table->boolean('is_lead')->default(false);
            $table->string('lead_stage_slug')->default('converted-to-client');
            $table->foreign('lead_stage_slug')->references('slug')->on('lfa_selections._lfa_lead_stage')->onUpdate('cascade');
            $table->string('sales_stage_slug')->default('new');
            $table->foreign('sales_stage_slug')->references('slug')->on('lfa_selections._lfa_sales_stage')->onUpdate('cascade');
            $table->string('source_slug')->default('others');
            $table->foreign('source_slug')->references('slug')->on('lfa_selections._lfa_client_sources')->onUpdate('cascade');

            $table->string('display_name')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_uen')->nullable();

            $table->text('description')->nullable();
            $table->text('interest')->nullable();
            $table->text('important')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('lfa_clients')->create('aliases', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('cascade');
            $table->string('full_name')->nullable();
            $table->string('nric_no')->nullable();
            $table->timestamps();
        });

        Schema::connection('lfa_clients')->create('life_assured', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // associate
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // Policy Holder
            $table->uuid('policy_holder_uuid')->nullable();
            $table->foreign('policy_holder_uuid')->references('uuid')->on('lfa_clients.clients')->onDelete('cascade');
            $table->string('relationship_type_slug')->nullable();
            $table->foreign('relationship_type_slug')->references('slug')->on('lfa_selections.relationship_types')->onUpdate('cascade');
            $table->uuid('individual_uuid');
            $table->foreign('individual_uuid')->references('uuid')->on('lfa_individuals.individuals')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('lfa_clients')->create('introducers', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // associate
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // client
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('clients')->onDelete('set null');
            $table->string('year', 4)->nullable();
            $table->date('date_start');
            $table->date('date_end');
            $table->boolean('gift_received')->default(false);
            $table->unsignedInteger('reference_pid')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('lfa_clients')->create('introducers_gifts', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('introducer_uuid');
            $table->foreign('introducer_uuid')->references('uuid')->on('introducers')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('serial')->nullable();
            $table->double('value', 15, 5)->default(0.00);
            $table->timestamps();
        });

        Schema::connection('lfa_clients')->create('nominees', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // associate
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            // client
            $table->uuid('client_uuid')->nullable();
            $table->foreign('client_uuid')->references('uuid')->on('clients')->onDelete('set null');
            $table->uuid('introducer_uuid')->nullable();
            $table->foreign('introducer_uuid')->references('uuid')->on('introducers')->onDelete('set null');
            $table->uuid('individual_uuid');
            $table->foreign('individual_uuid')->references('uuid')->on('lfa_individuals.individuals')->onDelete('cascade');
            $table->string('nominee_status_slug')->nullable();
            $table->foreign('nominee_status_slug')->references('slug')->on('lfa_selections._lfa_nominee_status')->onUpdate('cascade');
            $table->string('relationship_type_slug')->nullable();
            $table->foreign('relationship_type_slug')->references('slug')->on('lfa_selections.relationship_types')->onUpdate('cascade');
            $table->string('nominee_benefit_slug')->nullable();
            $table->foreign('nominee_benefit_slug')->references('slug')->on('lfa_selections._lfa_nominee_benefits')->onUpdate('cascade');
            // business
            $table->text('remarks')->nullable();
            $table->boolean('converted')->default(false);
            $table->uuid('converted_client_uuid')->nullable();
            $table->foreign('converted_client_uuid')->references('uuid')->on('clients')->onDelete('set null');
            $table->timestamps();
        });

        Schema::connection('lfa_clients')->create('sales_activities', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            // associate
            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates');
            // client
            $table->uuid('client_uuid');
            $table->foreign('client_uuid')->references('uuid')->on('clients');
            // activity
            $table->string('activity_slug');
            $table->foreign('activity_slug')->references('slug')->on('lfa_selections._lfa_sales_activities');
            $table->string('outcome_slug')->nullable();
            $table->foreign('outcome_slug')->references('slug')->on('lfa_selections._lfa_outcomes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_clients')->disableForeignKeyConstraints();
        Schema::connection('lfa_clients')->dropIfExists('sales_activities');
        Schema::connection('lfa_clients')->dropIfExists('nominees');
        Schema::connection('lfa_clients')->dropIfExists('introducers_gifts');
        Schema::connection('lfa_clients')->dropIfExists('introducers');
        Schema::connection('lfa_clients')->dropIfExists('life_assured');
        Schema::connection('lfa_clients')->dropIfExists('aliases');
        Schema::connection('lfa_clients')->dropIfExists('clients');
        Schema::connection('lfa_clients')->enableForeignKeyConstraints();
    }
}
