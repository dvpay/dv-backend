<?php

use App\Models\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            Currency::where('chain', 'trc20usdt')->update(['sort_order' => 1]);
            Currency::where('chain', 'btc')->update(['sort_order' => 2]);
            Currency::where('chain', 'trx')->update(['sort_order' => 3]);
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            //
        });
    }
};
