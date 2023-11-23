<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('model_id')->nullable();
            $table->string('model_type')->nullable();
            $table->string('name');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['model_id', 'model_type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
