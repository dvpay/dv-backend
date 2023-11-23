<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoice_histories', function (Blueprint $table) {
            $table->json('text_variables')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_histories', function (Blueprint $table) {
            $table->dropColumn('text_variables');
        });
    }
};
