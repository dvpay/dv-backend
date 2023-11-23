<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('withdrawal_wallet_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('withdrawal_wallet_id')
                ->constrained('withdrawal_wallets');
            $table->string('address');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_wallet_addresses');
    }
};
