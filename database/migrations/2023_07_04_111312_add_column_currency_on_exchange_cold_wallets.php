<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exchange_cold_wallets', function (Blueprint $table) {
            $table->string('currency');
        });
    }

    public function down(): void
    {
        Schema::table('exchange_cold_wallets', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
