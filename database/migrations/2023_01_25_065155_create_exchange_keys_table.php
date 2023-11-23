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
        Schema::create('exchange_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exchange_id')->nullable(false);

            $table->foreign('exchange_id')
                ->references('id')->on('exchanges')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('key')->nullable(false);
            $table->timestampsTz();

            $table->unique(['exchange_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_keys');
    }
};
