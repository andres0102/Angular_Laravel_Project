<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComments extends Migration
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
        Schema::connection('lfa__general')->create('comments', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->uuid('user_uuid')->nullable();
            $table->foreign('user_uuid')->references('uuid')->on('lfa_users.users')->onDelete('set null');
            $table->integer('commentable_id')->unsigned();
            $table->string('commentable_type');
            // Details
            $table->text('content');
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
        Schema::connection('lfa__general')->disableForeignKeyConstraints();
        Schema::connection('lfa__general')->dropIfExists('comments');
        Schema::connection('lfa__general')->enableForeignKeyConstraints();
    }
}
