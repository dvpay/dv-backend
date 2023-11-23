<?php

use App\Enums\Blockchain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('payer_id');
            $table->string('currency_id');
            $table->enum('blockchain', Blockchain::values())->nullable(false);
            $table->string('address');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('payer_id')
                    ->references('id')
                    ->on('payers');

            $table->foreign('currency_id')
                    ->references('id')
                    ->on('currencies');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payer_addresses');
    }
};
