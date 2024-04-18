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
        Schema::table('wallets', function (Blueprint $table) {
            $table->foreign(['exchange_id'])->references(['id'])->on('exchanges');
            $table->foreign(['store_id'])->references(['id'])->on('stores');
            $table->foreign(['user_id'])->references(['id'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign('wallets_exchange_id_foreign');
            $table->dropForeign('wallets_store_id_foreign');
            $table->dropForeign('wallets_user_id_foreign');
        });
    }
};
