<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('lfa_users')->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->string('password');
            // User Profile Data
            $table->string('designation_slug')->nullable();
            $table->foreign('designation_slug')->references('slug')->on('lfa_selections._lfa_designations')->onUpdate('cascade');
            $table->text('bio')->nullable();
            // LFA Staff Data
            $table->string('onboarding_status_slug')->nullable();
            $table->foreign('onboarding_status_slug')->references('slug')->on('lfa_selections._lfa_onboarding_status')->onUpdate('cascade');
            $table->uuid('individual_uuid')->nullable();
            $table->foreign('individual_uuid')->references('uuid')->on('lfa_individuals.individuals')->onDelete('set null');
            $table->uuid('spouse_uuid')->nullable();
            $table->foreign('spouse_uuid')->references('uuid')->on('lfa_individuals.individuals')->onDelete('set null');
            $table->uuid('associate_uuid')->nullable();
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->uuid('onboarded_by')->nullable();
            $table->uuid('offboarded_by')->nullable();
            $table->string('printer_id')->nullable();
            $table->string('did_no')->nullable();
            // Applicable dates
            $table->date('date_lfa_application')->nullable();
            $table->date('date_ceo_interview')->nullable();
            $table->date('date_contract_start')->nullable();
            $table->date('date_onboarded')->nullable();
            $table->date('date_offboarded')->nullable();
            $table->date('date_resigned')->nullable();
            $table->date('date_last_day')->nullable();
            // Boolean Checks
            $table->boolean('activated')->default(1);
            $table->boolean('setup')->default(0);
            $table->boolean('private')->default(0);
            $table->boolean('is_associate')->default(0);
            $table->boolean('is_staff')->default(0);
            $table->boolean('is_assistant')->default(0);
            $table->boolean('is_candidate')->default(0);
            $table->boolean('is_client')->default(0);
            $table->boolean('is_guest')->default(0);

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('lfa_users')->create('password_resets', function (Blueprint $table) {
            $table->uuid('user_uuid');
            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->string('email');
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection('lfa_users')->create('verify_email', function (Blueprint $table) {
            $table->uuid('user_uuid');
            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection('lfa_users')->create('devices', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('user_uuid');
            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->macAddress('mac')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('meraki_id')->nullable();
            $table->string('description')->nullable();
            $table->string('mdnsName')->nullable();
            $table->string('dhcpHostname')->nullable();
            $table->string('vlan')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('os')->nullable();
            $table->string('user')->nullable();
            $table->string('ssid')->nullable();
            $table->string('wirelessCapabilities')->nullable();
            $table->macAddress('recentDeviceMac')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->timestamp('firstSeen')->nullable();
            $table->timestamp('lastSeen')->nullable();
            $table->timestamps();
        });

        Schema::connection('lfa_users')->create('associates_access', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('user_uuid');
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('cascade');
            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['associate_uuid', 'user_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_users')->disableForeignKeyConstraints();
        Schema::connection('lfa_users')->dropIfExists('associates_access');
        Schema::connection('lfa_users')->dropIfExists('devices');
        Schema::connection('lfa_users')->dropIfExists('verify_email');
        Schema::connection('lfa_users')->dropIfExists('password_resets');
        Schema::connection('lfa_users')->dropIfExists('users');
        Schema::connection('lfa_users')->enableForeignKeyConstraints();
    }
}
