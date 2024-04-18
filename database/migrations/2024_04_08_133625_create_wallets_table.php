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
        Schema::create('wallets', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('address');
            $table->enum('blockchain', ['tron', 'bitcoin', 'ethereum'])->nullable();
            $table->string('chain');
            $table->char('store_id', 36)->nullable()->index('wallets_store_id_foreign');
            $table->boolean('readonly');
            $table->string('seed')->nullable();
            $table->string('pass_phrase')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->decimal('withdrawal_min_balance', 28, 8)->nullable()->default(0);
            $table->unsignedInteger('withdrawal_interval')->nullable()->default(0);
            $table->boolean('withdrawal_enabled')->default(false);
            $table->unsignedBigInteger('user_id')->nullable()->index('wallets_user_id_foreign');
            $table->boolean('enable_automatic_exchange')->default(false);
            $table->unsignedBigInteger('exchange_id')->nullable()->index('wallets_exchange_id_foreign');
            $table->string('withdrawal_interval_cron')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallets');
    }
};
