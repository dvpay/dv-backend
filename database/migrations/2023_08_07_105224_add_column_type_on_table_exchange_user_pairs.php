<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exchange_user_pairs', function (Blueprint $table) {
            $table->dropColumn('via');
            $table->string('type')->nullable(false)->default('sell');
        });
    }

    public function down(): void
    {
        Schema::table('exchange_user_pairs', function (Blueprint $table) {
            //
        });
    }
};
