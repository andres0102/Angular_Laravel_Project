<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('lfa_teams')->create('teams', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('type_slug')->nullable();
            $table->foreign('type_slug')->references('slug')->on('lfa_selections._lfa_associates_teams_types')->onUpdate('cascade');
            $table->uuid('owner_uuid')->nullable();
            $table->foreign('owner_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('lfa_teams')->create('memberships', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('team_uuid');
            $table->foreign('team_uuid')->references('uuid')->on('teams')->onDelete('cascade');
            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('cascade');
            $table->string('role_slug');
            $table->foreign('role_slug')->references('slug')->on('lfa_selections._lfa_associates_teams_roles')->onUpdate('cascade');
            $table->timestamps();
            $table->unique(['team_uuid', 'associate_uuid', 'role_slug']);
        });

        Schema::connection('lfa_teams')->create('invitations', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('team_uuid');
            $table->foreign('team_uuid')->references('uuid')->on('teams')->onDelete('cascade');
            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('lfa_associates.associates')->onDelete('cascade');
            $table->enum('type', ['invite', 'request']);
            $table->string('email')->nullable();
            $table->string('accept_token');
            $table->string('deny_token');
            $table->timestamps();
            $table->unique(['team_uuid', 'associate_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_teams')->disableForeignKeyConstraints();
        Schema::connection('lfa_teams')->dropIfExists('memberships');
        Schema::connection('lfa_teams')->dropIfExists('invitations');
        Schema::connection('lfa_teams')->dropIfExists('teams');
        Schema::connection('lfa_teams')->enableForeignKeyConstraints();
    }
}
