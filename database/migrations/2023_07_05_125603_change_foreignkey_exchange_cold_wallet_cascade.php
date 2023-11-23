<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exchange_cold_wallet_withdrawals', function (Blueprint $table) {
            $table->dropForeign(['exchange_cold_wallet_id']);
        });
    }

    public function down(): void
    {
        Schema::table('exchange_cold_wallet_withdrawals', function (Blueprint $table) {
            //
        });
    }
};
