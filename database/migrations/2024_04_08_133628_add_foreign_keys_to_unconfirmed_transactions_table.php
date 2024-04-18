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
        Schema::table('unconfirmed_transactions', function (Blueprint $table) {
            $table->foreign(['invoice_id'])->references(['id'])->on('invoices');
            $table->foreign(['payer_id'])->references(['id'])->on('payers');
            $table->foreign(['store_id'])->references(['id'])->on('stores');
            $table->foreign(['user_id'])->references(['id'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unconfirmed_transactions', function (Blueprint $table) {
            $table->dropForeign('unconfirmed_transactions_invoice_id_foreign');
            $table->dropForeign('unconfirmed_transactions_payer_id_foreign');
            $table->dropForeign('unconfirmed_transactions_store_id_foreign');
            $table->dropForeign('unconfirmed_transactions_user_id_foreign');
        });
    }
};
