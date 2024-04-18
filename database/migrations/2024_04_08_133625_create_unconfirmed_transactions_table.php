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
        Schema::create('unconfirmed_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('unconfirmed_transactions_user_id_foreign');
            $table->char('store_id')->nullable()->index('unconfirmed_transactions_store_id_foreign');
            $table->char('invoice_id')->nullable()->index('unconfirmed_transactions_invoice_id_foreign');
            $table->string('from_address');
            $table->string('to_address');
            $table->string('tx_id');
            $table->string('currency_id');
            $table->timestamps();
            $table->decimal('amount', 28, 8)->default(0);
            $table->decimal('amount_usd', 28, 8)->default(0);
            $table->char('payer_id', 36)->nullable()->index('unconfirmed_transactions_payer_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unconfirmed_transactions');
    }
};
