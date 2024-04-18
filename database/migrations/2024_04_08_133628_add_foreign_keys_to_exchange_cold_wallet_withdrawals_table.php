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
        Schema::table('exchange_cold_wallet_withdrawals', function (Blueprint $table) {
            $table->foreign(['exchange_id'])->references(['id'])->on('exchanges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_cold_wallet_withdrawals', function (Blueprint $table) {
            $table->dropForeign('exchange_cold_wallet_withdrawals_exchange_id_foreign');
        });
    }
};
