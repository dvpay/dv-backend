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
        Schema::create('exchange_withdrawal_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('exchange_id');
            $table->bigInteger('user_id');
            $table->string('address');
            $table->boolean('is_withdrawal_enable');
            $table->decimal('min_balance');
            $table->string('chain', 36);
            $table->string('currency', 24);
            $table->timestamps();
            $table->decimal('current_balance', 28, 8)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_withdrawal_wallets');
    }
};
