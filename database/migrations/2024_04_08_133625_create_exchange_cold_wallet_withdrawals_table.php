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
        Schema::create('exchange_cold_wallet_withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exchange_cold_wallet_id')->index('exchange_cold_wallet_withdrawals_exchange_cold_wallet_id_foreign');
            $table->string('address');
            $table->decimal('amount', 28, 8);
            $table->unsignedBigInteger('exchange_id')->nullable()->index('exchange_cold_wallet_withdrawals_exchange_id_foreign');
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
        Schema::dropIfExists('exchange_cold_wallet_withdrawals');
    }
};
