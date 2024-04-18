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
        Schema::table('exchange_transactions', function (Blueprint $table) {
            $table->foreign(['from_currency_id'])->references(['id'])->on('currencies');
            $table->foreign(['to_currency_id'])->references(['id'])->on('currencies');
            $table->foreign(['user_id'])->references(['id'])->on('users');
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
        Schema::table('exchange_transactions', function (Blueprint $table) {
            $table->dropForeign('exchange_transactions_from_currency_id_foreign');
            $table->dropForeign('exchange_transactions_to_currency_id_foreign');
            $table->dropForeign('exchange_transactions_user_id_foreign');
            $table->dropForeign('exchange_transactions_wallet_id_foreign');
        });
    }
};
