<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exchange_wallet_currencies', function (Blueprint $table) {
            $table->dropForeign('exchange_wallet_currencies_from_currency_id_foreign');
            $table->dropForeign('exchange_wallet_currencies_to_currency_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::table('exchange_wallet_currencies', function (Blueprint $table) {
            $table->foreign('from_currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('to_currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }
};
