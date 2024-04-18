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
            $table->bigIncrements('id');
            $table->char('wallet_id', 36)->index('exchange_wallet_currencies_wallet_id_foreign');
            $table->string('from_currency_id')->index('exchange_wallet_currencies_from_currency_id_foreign');
            $table->string('to_currency_id')->index('exchange_wallet_currencies_to_currency_id_foreign');
            $table->string('via')->nullable();
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
        Schema::dropIfExists('exchange_wallet_currencies');
    }
};
