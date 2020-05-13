<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** ===================================================================================================
         * Note(s) tagged to each Model
         */
        Schema::connection('lfa__general')->create('notes', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            $table->integer('notable_id')->unsigned();
            $table->string('notable_type');
            // Details
            $table->text('content')->nullable();
            $table->timestamps();
        });

        Schema::connection('lfa__general')->create('conversations', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            $table->integer('conversable_id')->unsigned();
            $table->string('conversable_type');
            // Details
            $table->string('activity_type_slug')->nullable();
            $table->foreign('activity_type_slug')->references('slug')->on('lfa_selections._lfa_sales_activities')->onUpdate('cascade');
            $table->string('outcome_slug')->nullable();
            $table->foreign('outcome_slug')->references('slug')->on('lfa_selections._lfa_outcomes')->onUpdate('cascade');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });


        /** ===================================================================================================
         * Task(s) tagged to each Model
         */
        Schema::connection('lfa__general')->create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            $table->integer('taskable_id')->unsigned();
            $table->string('taskable_type');
            // Details
            $table->string('outcome_slug')->nullable();
            $table->foreign('outcome_slug')->references('slug')->on('lfa_selections._lfa_outcomes')->onUpdate('cascade');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });


        /** ===================================================================================================
         * Appointment(s) tagged to each Model
         */
        Schema::connection('lfa__general')->create('appointments', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            $table->integer('appointmentable_id')->unsigned();
            $table->string('appointmentable_type');
            // Details
            $table->string('outcome_slug')->nullable();
            $table->foreign('outcome_slug')->references('slug')->on('lfa_selections._lfa_outcomes')->onUpdate('cascade');
            $table->string('title')->nullable();
            $table->text('details')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('full_day')->default(false);
            $table->timestamps();
        });


        /** ===================================================================================================
         * Activity Log(s) tagged to each Model
         */
        Schema::connection('lfa__general')->create('logs', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            $table->integer('loggable_id')->unsigned();
            $table->string('loggable_type');
            // Details
            $table->string('event');
            $table->string('title');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('target_model')->nullable();
            $table->uuid('target_uuid')->nullable();
            $table->timestamps();
        });

        /** ===================================================================================================
         *  - Notice Details -- To show in Firm/Team Noticeboard(s)
         */
        Schema::connection('lfa__general')->create('notices', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('team_uuid')->nullable();          // Null = Firm-wide
            $table->foreign('team_uuid')->references('uuid')->on('lfa_teams.teams')->onDelete('cascade');
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            // Notice Details
            $table->string('title')->nullable();
            $table->text('details')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('full_day')->default(false);
            $table->boolean('private')->default(0);         // Non-Private = Shown Automatically to everyone in Firm/Team, Private = Only visible to selected user(s) in Firm/Team
            $table->boolean('important')->default(0);       // Important Icon
            $table->boolean('sticky')->default(0);          // Sticky on Top == Show First
            $table->timestamps();
        });


        /** ===================================================================================================
         *  - Notice Privacy Membership -- For private notice(s)
         */
        Schema::connection('lfa__general')->create('notices_privacy', function (Blueprint $table) {
            $table->uuid('notice_uuid');
            $table->foreign('notice_uuid')->references('uuid')->on('notices')->onDelete('cascade');
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            $table->unique(['notice_uuid', 'user_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa__general')->disableForeignKeyConstraints();
        Schema::connection('lfa__general')->dropIfExists('notices_privacy');
        Schema::connection('lfa__general')->dropIfExists('notices');
        Schema::connection('lfa__general')->dropIfExists('logs');
        Schema::connection('lfa__general')->dropIfExists('appointments');
        Schema::connection('lfa__general')->dropIfExists('tasks');
        Schema::connection('lfa__general')->dropIfExists('conversations');
        Schema::connection('lfa__general')->dropIfExists('notes');
        Schema::connection('lfa__general')->enableForeignKeyConstraints();
    }
}
