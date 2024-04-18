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
        Schema::table('exchange_wallet_currencies', function (Blueprint $table) {
            $table->foreign(['wallet_id'])->references(['id'])->on('wallets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_wallet_currencies', function (Blueprint $table) {
            $table->dropForeign('exchange_wallet_currencies_wallet_id_foreign');
        });
    }
};
