<?php

use App\Enums\Blockchain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hot_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('currency_id');
            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies');
            $table->foreignId('user_id')
                ->constrained();
            $table->string('address', 64)
                ->nullable(false);

            $table->enum('blockchain', Blockchain::values())->nullable(false);
            $table->string('state', 16);
            $table->timestamps();

            $table->index(['user_id', 'address']);
            $table->unique(['currency_id', 'address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hot_wallets');
    }
};
