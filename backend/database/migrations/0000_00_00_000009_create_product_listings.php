<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductListings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** ===================================================================================================
         * Main Products
         */
        Schema::connection('lfa_products')->create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            // Product Details
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            // Product Variables
            $table->char('currency', 3)->default('SGD');
            $table->boolean('tax')->default(1);
            // References
            $table->string('reference_uen')->nullable();
            $table->text('reference_url')->nullable();
            $table->string('reference_thumbnail')->nullable();
            $table->string('reference_brochure')->nullable();
            $table->boolean('activated')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Product - (Sub) Options
         */
        Schema::connection('lfa_products')->create('product_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            $table->uuid('product_uuid');
            $table->foreign('product_uuid')->references('uuid')->on('products')->onDelete('cascade');
            $table->string('product_cat_slug')->nullable();
            $table->foreign('product_cat_slug')->references('slug')->on('lfa_selections._lfa_product_categories')->onUpdate('cascade');
            // Product Details
            $table->string('name');
            // Product Variables
            $table->string('premium_type', 30)->nullable()->default('regular'); // single or regular or both
            $table->boolean('participating')->default(false);
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            // References
            $table->string('reference_uen')->nullable();
            $table->unsignedInteger('reference_pid')->nullable();
            $table->boolean('activated')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Riders
         */
        Schema::connection('lfa_products')->create('riders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            // Foreign References
            $table->string('provider_slug')->nullable();
            $table->foreign('provider_slug')->references('slug')->on('lfa_selections._lfa_providers')->onUpdate('cascade');
            // Rider Details
            $table->string('name');
            // References
            $table->string('reference_uen')->nullable();
            $table->unsignedInteger('reference_pid')->nullable();
            $table->boolean('activated')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        /** ===================================================================================================
         * Riders
         */
        Schema::connection('lfa_products')->create('product_options_has_riders', function (Blueprint $table) {
            $table->uuid('product_option_uuid');
            $table->foreign('product_option_uuid')->references('uuid')->on('product_options')->onDelete('cascade');
            $table->uuid('rider_uuid');
            $table->foreign('rider_uuid')->references('uuid')->on('riders')->onDelete('cascade');
            $table->unique(['product_option_uuid', 'rider_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('lfa_products')->disableForeignKeyConstraints();
        Schema::connection('lfa_products')->dropIfExists('product_options_has_riders');
        Schema::connection('lfa_products')->dropIfExists('product_options');
        Schema::connection('lfa_products')->dropIfExists('riders');
        Schema::connection('lfa_products')->dropIfExists('products');
        Schema::connection('lfa_products')->enableForeignKeyConstraints();
    }
}
