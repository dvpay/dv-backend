<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('service_logs', 'service_id')) {
            Schema::table('service_logs', function (Blueprint $table) {
                $table->dropForeign('service_logs_service_id_foreign');
                $table->dropColumn('service_id');
            });
        }
    }
};
