<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exchange_transactions', function (Blueprint $table) {
            $table->decimal('left_amount', 28, 8)->after('amount_usd');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_transactions', function (Blueprint $table) {
            $table->dropColumn('left_amount');
        });
    }
};
