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
            $table->foreign(['currency_id'])->references(['id'])->on('currencies');
            $table->foreign(['invoice_id'])->references(['id'])->on('invoices');
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
            $table->dropForeign('invoice_addresses_currency_id_foreign');
            $table->dropForeign('invoice_addresses_invoice_id_foreign');
        });
    }
};
