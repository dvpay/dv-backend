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
        Schema::table('wallet_balances', function (Blueprint $table) {
            $table->foreign(['currency_id'])->references(['id'])->on('currencies');
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
        Schema::table('wallet_balances', function (Blueprint $table) {
            $table->dropForeign('wallet_balances_currency_id_foreign');
            $table->dropForeign('wallet_balances_wallet_id_foreign');
        });
    }
};
