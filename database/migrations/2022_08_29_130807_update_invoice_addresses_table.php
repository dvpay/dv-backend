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
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->string('blockchain_currency_code')->nullable(false)->default('BTC');

            $table->foreign('blockchain_currency_code')
                ->references('blockchain_currency_code')->on('blockchain_currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->decimal('balance', 28, 8)->nullable(false)->default(0);
            $table->decimal('rate', 28, 8)->nullable(false);
            $table->string('invoice_currency_code')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->dropForeign('invoice_addresses_blockchain_currency_code_foreign');

            $table->dropColumn('blockchain_currency_code');
            $table->dropColumn('balance');
            $table->dropColumn('rate');
            $table->dropColumn('invoice_currency_code');
        });
    }
};
