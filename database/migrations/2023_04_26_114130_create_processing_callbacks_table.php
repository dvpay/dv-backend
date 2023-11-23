<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('processing_callbacks', function (Blueprint $table) {
            $table->id();
            $table->text('request');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_callbacks');
    }
};
