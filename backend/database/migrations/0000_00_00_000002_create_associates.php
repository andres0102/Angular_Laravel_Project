<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssociates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('lfa_associates')->create('associates', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->string('rnf_status_slug')->nullable();
            $table->foreign('rnf_status_slug')->references('slug')->on('lfa_selections._lfa_associates_rnf_status')->onUpdate('cascade');
            $table->string('aa_code')->nullable();
            $table->string('lfa_sl_no')->nullable();
            // $table->string('lfa_email')->nullable();
            $table->string('applicant_code')->nullable();
            $table->string('rnf_no')->nullable();

            $table->boolean('eligible_life')->default(false);
            $table->boolean('eligible_health')->default(false);
            $table->boolean('eligible_ilp')->default(false);
            $table->boolean('eligible_cis')->default(false);
            $table->boolean('eligible_gi')->default(false);
            $table->boolean('eligible_medishield')->default(false);

            $table->date('date_rnf_submission')->nullable();
            $table->date('date_rnf_approval')->nullable();
            $table->date('date_rnf_withdrawal')->nullable();
            $table->date('date_rnf_cessation')->nullable();

            $table->date('date_m9')->nullable();
            $table->date('date_m9a')->nullable();
            $table->date('date_m5')->nullable();
            $table->date('date_hi')->nullable();
            $table->date('date_m8')->nullable();
            $table->date('date_m8a')->nullable();
            $table->date('date_cert_ilp')->nullable();
            $table->date('date_cert_li')->nullable();
            $table->date('date_cert_fna')->nullable();
            $table->date('date_cert_bcp')->nullable();
            $table->date('date_cert_pgi')->nullable();
            $table->date('date_cert_comgi')->nullable();
            $table->date('date_cert_cgi')->nullable();
            $table->string('cert_pro')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('lfa_associates')->create('movements', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('associates')->onDelete('cascade');
            $table->uuid('reporting_uuid')->nullable();
            $table->foreign('reporting_uuid')->references('uuid')->on('associates')->onDelete('cascade');
            $table->string('designation_slug')->nullable();
            $table->foreign('designation_slug')->references('slug')->on('lfa_selections._lfa_designations')->onUpdate('cascade');

            $table->string('aa_code')->nullable();
            $table->string('lfa_code')->nullable();
            $table->date('date_start');
            $table->date('date_end');
            $table->timestamps();
        });

        Schema::connection('lfa_associates')->create('bandings_lfa', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('associates')->onDelete('cascade');

            $table->unsignedInteger('banding_type')->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->decimal('rate', 8, 2);
            $table->date('date_start');
            $table->date('date_end');
            $table->timestamps();
        });

        Schema::connection('lfa_associates')->create('bandings_gi', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('associates')->onDelete('cascade');

            $table->unsignedInteger('rank');
            $table->date('date_start');
            $table->date('date_end');
            $table->timestamps();
        });

        Schema::connection('lfa_associates')->create('providers_codes', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->uuid('uuid')->unique();

            $table->uuid('associate_uuid');
            $table->foreign('associate_uuid')->references('uuid')->on('associates')->onDelete('cascade');
            $table->string('provider_slug');
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('code');
            $table->timestamps();
            $table->unique(['associate_uuid', 'provider_slug', 'code']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_associates')->disableForeignKeyConstraints();
        Schema::connection('lfa_associates')->dropIfExists('providers_codes');
        Schema::connection('lfa_associates')->dropIfExists('devices');
        Schema::connection('lfa_associates')->dropIfExists('bandings_gi');
        Schema::connection('lfa_associates')->dropIfExists('bandings_lfa');
        Schema::connection('lfa_associates')->dropIfExists('movements');
        Schema::connection('lfa_associates')->dropIfExists('associates');
        Schema::connection('lfa_associates')->enableForeignKeyConstraints();
    }
}
