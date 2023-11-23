<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('exchange_cold_wallets', function (Blueprint $table) {
			$table->id();

			$table->char('wallet_id', 36)->nullable(false)->unique();
			$table->string('address')->nullable(false);
			$table->boolean('is_withdrawal_enabled')->nullable(false)->default(true);
			$table->decimal('withdrawal_min_balance')->nullable();

			$table->foreign('wallet_id')
			      ->references('id')->on('wallets')
			      ->onDelete('restrict')
			      ->onUpdate('restrict');

			$table->timestampsTz();
		});

		Schema::create('exchange_cold_wallet_withdrawals', function (Blueprint $table) {
			$table->id();

			$table->bigInteger('exchange_cold_wallet_id')->unsigned()->nullable(false);
			$table->string('address')->nullable(false);
			$table->decimal('amount', 28, 8);
			$table->unsignedBigInteger('exchange_id')->nullable();

			$table->foreign('exchange_cold_wallet_id')
			      ->references('id')->on('exchange_cold_wallets')
			      ->onDelete('restrict')
			      ->onUpdate('restrict');

			$table->foreign('exchange_id')
			      ->references('id')->on('exchanges')
			      ->onDelete('restrict')
			      ->onUpdate('restrict');

			$table->timestampsTz();
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
		Schema::dropIfExists('exchange_cold_wallets');
	}
};
