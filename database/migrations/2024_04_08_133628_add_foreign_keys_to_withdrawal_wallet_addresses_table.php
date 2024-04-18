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
        Schema::table('withdrawal_wallet_addresses', function (Blueprint $table) {
            $table->foreign(['withdrawal_wallet_id'])->references(['id'])->on('withdrawal_wallets')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withdrawal_wallet_addresses', function (Blueprint $table) {
            $table->dropForeign('withdrawal_wallet_addresses_withdrawal_wallet_id_foreign');
        });
    }
};
