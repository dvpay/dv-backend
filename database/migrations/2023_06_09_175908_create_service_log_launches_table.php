<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_log_launches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable(false);

            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('status', 15);

            $table->dateTime('start_at')->nullable();
            $table->dateTime('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_log_launches');
    }
};
