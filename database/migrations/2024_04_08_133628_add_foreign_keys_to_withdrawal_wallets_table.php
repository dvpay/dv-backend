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
        Schema::table('withdrawal_wallets', function (Blueprint $table) {
            $table->foreign(['exchange_id'])->references(['id'])->on('exchanges')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withdrawal_wallets', function (Blueprint $table) {
            $table->dropForeign('withdrawal_wallets_exchange_id_foreign');
            $table->dropForeign('withdrawal_wallets_user_id_foreign');
        });
    }
};
