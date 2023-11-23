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
        Schema::create('exchange_dictionaries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('exchange_id')->nullable();

            $table->foreign('exchange_id')
                ->references('id')->on('exchanges')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('from_currency_id')->nullable(false);

            $table->foreign('from_currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('to_currency_id')->nullable(false);

            $table->foreign('to_currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->decimal('min_quantity')->nullable(false)->default(0);

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
        Schema::dropIfExists('exchange_dictionaries');
    }
};
