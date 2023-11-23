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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id')->nullable(false);

            $table->foreign('store_id')
                ->references('id')->on('stores')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('url')->nullable(false);
            $table->string('secret')->nullable(false);
            $table->boolean('enabled')->nullable(false)->default(true);
            $table->json('events');
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
        Schema::dropIfExists('webhooks');
    }
};
