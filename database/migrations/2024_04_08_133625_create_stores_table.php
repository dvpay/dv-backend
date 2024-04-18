<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('site')->nullable()->comment('Website of the user\'s store');
            $table->string('currency_id')->index('stores_currency_id_foreign')->comment('Default currency rate');
            $table->string('rate_source')->index('stores_rate_source_foreign')->comment('Place where get currency rate');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('invoice_expiration_time')->nullable()->comment('Expiration time in minutes');
            $table->char('processing_owner_id', 36)->nullable()->comment('processing owner\'s id');
            $table->string('return_url')->nullable();
            $table->string('success_url')->nullable();
            $table->unsignedSmallInteger('address_hold_time')->default(360);
            $table->decimal('rate_scale', 4)->default(1);
            $table->boolean('status')->default(true);
            $table->boolean('static_addresses')->default(false);
            $table->decimal('minimal_payment', 28)->default(0.1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
};
