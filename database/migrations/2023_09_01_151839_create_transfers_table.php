<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('user_id')->constrained();
            $table->string('currency_id');
            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies');

            $table->string('status');
            $table->string('address_from');
            $table->string('address_to');
            $table->timestamps();

            $table->index(['uuid', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
