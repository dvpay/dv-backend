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
		Schema::table('user_invoice_addresses', function (Blueprint $table) {
			$table->dropForeign(['processing_owner_id']);
		});

		DB::statement('alter table user_invoice_addresses modify state varchar(16) not null');
		DB::statement('alter table user_invoice_addresses modify processing_owner_id char(36) null');

		Schema::table('user_invoice_addresses', function (Blueprint $table) {

			$table->index('state');

			$table->foreign('processing_owner_id')
			      ->references('processing_owner_id')->on('users')
			      ->onDelete('restrict')
			      ->onUpdate('restrict');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_invoice_addresses', function (Blueprint $table) {
			$table->dropForeign(['processing_owner_id']);
			$table->dropIndex(['state']);
		});

		DB::statement('alter table user_invoice_addresses modify state varchar(255) not null');
		DB::statement('alter table user_invoice_addresses modify processing_owner_id varchar(255) null');

		Schema::table('user_invoice_addresses', function (Blueprint $table) {
			$table->foreign('processing_owner_id')
			      ->references('processing_owner_id')->on('users')
			      ->onDelete('restrict')
			      ->onUpdate('restrict');
		});
	}
};
