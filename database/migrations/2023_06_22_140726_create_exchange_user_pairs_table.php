<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exchange_user_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_id')
                ->constrained('exchanges');
            $table->foreignId('user_id')
                ->constrained('users');
            $table->string('currency_from');
            $table->string('currency_to');
            $table->string('symbol');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_user_pairs');
    }
};
