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
        Schema::create('exchange_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('exchange_transactions_user_id_foreign');
            $table->char('wallet_id', 36)->index('exchange_transactions_wallet_id_foreign');
            $table->string('from_currency_id')->index('exchange_transactions_from_currency_id_foreign');
            $table->string('to_currency_id')->index('exchange_transactions_to_currency_id_foreign');
            $table->decimal('amount', 28, 8);
            $table->decimal('amount_usd', 28);
            $table->decimal('left_amount', 28, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_transactions');
    }
};
