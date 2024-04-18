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
        Schema::table('invoice_status_histories', function (Blueprint $table) {
            $table->foreign(['invoice_id'], 'invoice_status_history_invoice_id_foreign')->references(['id'])->on('invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_status_histories', function (Blueprint $table) {
            $table->dropForeign('invoice_status_history_invoice_id_foreign');
        });
    }
};
