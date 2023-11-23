<?php

use App\Enums\Blockchain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->string('store_user_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('store_id')
                    ->references('id')
                    ->on('stores');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payers');
    }
};
