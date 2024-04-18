<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// delete before deploy on github
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('stores', 'minimal_payment')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->decimal('minimal_payment', 28)->default(0.1);
            });
        }
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('minimal_payment');
        });
    }
};
