<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->timestamp('exchange_rate_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->dropColumn('exchange_rate_at');
        });
    }
};