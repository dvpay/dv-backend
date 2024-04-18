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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign(['currency_id'])->references(['id'])->on('currencies');
            $table->foreign(['payer_id'])->references(['id'])->on('payers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['store_id'])->references(['id'])->on('stores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_currency_id_foreign');
            $table->dropForeign('invoices_payer_id_foreign');
            $table->dropForeign('invoices_store_id_foreign');
        });
    }
};
