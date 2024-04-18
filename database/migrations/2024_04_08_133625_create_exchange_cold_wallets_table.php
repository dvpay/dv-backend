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
        Schema::create('exchange_cold_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('wallet_id', 36);
            $table->string('address');
            $table->boolean('is_withdrawal_enabled')->default(true);
            $table->decimal('withdrawal_min_balance')->nullable();
            $table->timestamps();
            $table->string('chain', 36)->default('trc20usdt');
            $table->string('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_cold_wallets');
    }
};
