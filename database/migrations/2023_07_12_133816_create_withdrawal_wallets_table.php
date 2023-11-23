<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawal_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('exchange_id')->nullable()->constrained('exchanges');
            $table->string('chain');
            $table->string('blockchain');
            $table->string('currency');
            $table->boolean('withdrawal_enabled');
            $table->bigInteger('withdrawal_min_balance');
            $table->string('withdrawal_interval')->nullable();
            $table->string('type', 64)
                ->default('manual');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_wallets');
    }
};
