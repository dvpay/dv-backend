<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ExchangeChainType;

return new class extends Migration {
    public function up()
    {
        Schema::table('exchange_cold_wallets', function (Blueprint $table) {
            $table->string('chain', 36)->default(ExchangeChainType::TRC20USDT->value);
        });
    }

    public function down()
    {
        Schema::table('exchange_cold_wallets', function (Blueprint $table) {
            $table->dropColumn('chain');
        });
    }
};