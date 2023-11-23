<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_logs', function (Blueprint $table) {
            $table->string('log_id', 36)->nullable();
            $table->bigInteger('memory')->nullable();
            $table->index('log_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_logs', function (Blueprint $table) {
            $table->dropIndex(['log_id']);
            $table->dropColumn('log_id');
            $table->dropColumn('memory');
        });
    }
};
