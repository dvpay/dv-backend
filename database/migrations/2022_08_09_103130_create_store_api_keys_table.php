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
        Schema::create('store_api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id')->nullable(false);

            $table->foreign('store_id')
                ->references('id')->on('stores')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('key')->nullable(false);
            $table->boolean('enabled')->default(true)->nullable(false);

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_api_keys');
    }
};
