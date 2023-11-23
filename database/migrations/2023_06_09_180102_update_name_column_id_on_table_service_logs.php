<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_logs', function (Blueprint $table) {
            $table->dropColumn('status');

            $table->foreignId('service_log_launch_id')
                ->nullable()
                ->constrained('service_log_launches');
        });
    }

    public function down(): void
    {
        Schema::table('service_logs', function (Blueprint $table) {
            $table->dropForeign('service_log_launches');
            $table->string('status', 15);

            $table->string('log_id', 36)->nullable();
            $table->index('log_id');
        });
    }
};
