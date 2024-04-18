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
        Schema::create('withdrawal_wallets', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('user_id')->index('withdrawal_wallets_user_id_foreign');
            $table->unsignedBigInteger('exchange_id')->nullable()->index('withdrawal_wallets_exchange_id_foreign');
            $table->string('chain');
            $table->string('blockchain');
            $table->string('currency');
            $table->boolean('withdrawal_enabled');
            $table->bigInteger('withdrawal_min_balance');
            $table->string('withdrawal_interval')->nullable();
            $table->string('type', 64)->default('manual');
            $table->softDeletes();
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
        Schema::dropIfExists('withdrawal_wallets');
    }
};
