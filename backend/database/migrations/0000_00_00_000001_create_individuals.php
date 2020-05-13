<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndividuals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('lfa_individuals')->create('individuals', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->string('full_name')->nullable();
            $table->string('alias')->nullable();
            $table->string('chinese_name')->nullable();
            $table->string('nric_no')->nullable();
            $table->string('fin_no')->nullable();
            $table->string('passport_no')->nullable();
            $table->date('date_birth')->nullable();

            $table->string('salutation_slug')->nullable();
            $table->foreign('salutation_slug')->references('slug')->on('lfa_selections.salutations')->onUpdate('cascade');
            $table->string('gender_slug')->nullable();
            $table->foreign('gender_slug')->references('slug')->on('lfa_selections.genders')->onUpdate('cascade');
            $table->string('marital_status_slug')->nullable();
            $table->foreign('marital_status_slug')->references('slug')->on('lfa_selections.marital_status')->onUpdate('cascade');
            $table->string('race_slug')->nullable();
            $table->foreign('race_slug')->references('slug')->on('lfa_selections.race')->onUpdate('cascade');
            $table->string('country_birth_slug')->nullable();
            $table->foreign('country_birth_slug')->references('slug')->on('lfa_selections.countries')->onUpdate('cascade');
            $table->string('nationality_slug')->nullable();
            $table->foreign('nationality_slug')->references('slug')->on('lfa_selections.countries')->onUpdate('cascade');
            $table->string('residency_status_slug')->nullable();
            $table->foreign('residency_status_slug')->references('slug')->on('lfa_selections.residency_status')->onUpdate('cascade');
            $table->string('employment_status_slug')->nullable();
            $table->foreign('employment_status_slug')->references('slug')->on('lfa_selections.employment_status')->onUpdate('cascade');
            $table->string('job_title')->nullable();
            $table->string('company_name')->nullable();
            $table->string('business_nature')->nullable();
            $table->unsignedMediumInteger('income_range')->default(0);
            $table->string('education_level_slug')->nullable();
            $table->foreign('education_level_slug')->references('slug')->on('lfa_selections.educational_levels')->onUpdate('cascade');
            $table->string('education_institution')->nullable();
            $table->string('field_of_study')->nullable();
            $table->boolean('smoker')->default(0);
            $table->boolean('selected')->default(0);
            $table->boolean('pdpa')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Individuals - Contact Details
         */
        Schema::connection('lfa_individuals')->create('contacts', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('individual_uuid');
            $table->foreign('individual_uuid')->references('uuid')->on('individuals')->onDelete('cascade');
            $table->string('contact_type_slug')->default('default');
            $table->foreign('contact_type_slug')->references('slug')->on('lfa_selections.contact_types')->onUpdate('cascade');
            $table->string('home_no')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('fax_no')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->unique(['individual_uuid', 'contact_type_slug']);
        });

        /** ===================================================================================================
         * Individuals - Address Details
         */
        Schema::connection('lfa_individuals')->create('addresses', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('individual_uuid');
            $table->foreign('individual_uuid')->references('uuid')->on('individuals')->onDelete('cascade');
            $table->string('address_type_slug')->default('residential');
            $table->foreign('address_type_slug')->references('slug')->on('lfa_selections.address_types')->onUpdate('cascade');
            $table->string('block')->nullable();
            $table->string('street')->nullable();
            $table->string('unit')->nullable();
            $table->string('building')->nullable();
            $table->string('city')->nullable();
            $table->string('postal')->nullable();
            $table->string('country_slug')->nullable();
            $table->foreign('country_slug')->references('slug')->on('lfa_selections.countries')->onUpdate('cascade');
            $table->timestamps();
            $table->unique(['individual_uuid', 'address_type_slug']);
        });

        /** ===================================================================================================
         * Individuals - Bank Account Details
         */
        Schema::connection('lfa_individuals')->create('banks', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->uuid('individual_uuid');
            $table->foreign('individual_uuid')->references('uuid')->on('individuals')->onDelete('cascade');
            $table->string('bank_slug')->nullable();
            $table->foreign('bank_slug')->references('slug')->on('lfa_selections.banks')->onUpdate('cascade');
            $table->string('account_no')->nullable();
            $table->timestamps();
            $table->unique(['individual_uuid', 'bank_slug', 'account_no']);
        });


        /** ===================================================================================================
         * Individuals - Dependents
         */
        Schema::connection('lfa_individuals')->create('individuals_has_dependents', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('individual_uuid');
            $table->foreign('individual_uuid')->references('uuid')->on('individuals')->onDelete('cascade');
            $table->uuid('dependent_uuid');
            $table->foreign('dependent_uuid')->references('uuid')->on('individuals')->onDelete('cascade');
            $table->unique(['individual_uuid', 'dependent_uuid'], 'unique_dependent');
            $table->string('relationship_type_slug')->nullable();
            $table->foreign('relationship_type_slug')->references('slug')->on('lfa_selections.relationship_types')->onUpdate('cascade');
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
        Schema::connection('lfa_individuals')->disableForeignKeyConstraints();
        Schema::connection('lfa_individuals')->dropIfExists('individuals_has_dependents');
        Schema::connection('lfa_individuals')->dropIfExists('banks');
        Schema::connection('lfa_individuals')->dropIfExists('addresses');
        Schema::connection('lfa_individuals')->dropIfExists('contacts');
        Schema::connection('lfa_individuals')->dropIfExists('individuals');
        Schema::connection('lfa_individuals')->enableForeignKeyConstraints();
    }
}
