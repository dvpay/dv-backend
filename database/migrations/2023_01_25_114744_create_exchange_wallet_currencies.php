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
        Schema::create('exchange_wallet_currencies', function (Blueprint $table) {
            $table->id();
            $table->uuid('wallet_id')->nullable(false);

            $table->foreign('wallet_id')
                ->references('id')->on('wallets')
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
        Schema::dropIfExists('exchange_wallet_currencies');
    }
};
