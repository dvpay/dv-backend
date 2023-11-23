<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exchange_withdrawal_wallets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('exchange_id');
            $table->bigInteger('user_id');
            $table->string('address');
            $table->boolean('is_withdrawal_enable');
            $table->decimal('min_balance', 8, 2);
            $table->string('chain', 36);
            $table->string('currency', 24);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_withdrawal_wallets');
    }
};
