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
        Schema::table('user_invoice_addresses', function (Blueprint $table) {
            $table->float('balance_usd')->nullable(false)->after('balance');
            $table->string('currency_id')->nullable(false)->after('balance_usd');

            $table->foreign('currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_invoice_addresses', function (Blueprint $table) {
            $table->dropColumn('balance_usd');

            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });
    }
};
