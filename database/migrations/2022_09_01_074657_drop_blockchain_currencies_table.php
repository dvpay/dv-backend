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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        /**
         * Drop old foreign keys
         */
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->dropForeign('invoice_addresses_blockchain_currency_code_foreign');
            $table->dropIndex('invoice_addresses_blockchain_currency_code_foreign');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign('stores_currency_foreign');
            $table->dropIndex('stores_currency_foreign');
        });

        Schema::table('wallet_balances', function (Blueprint $table) {
            $table->dropForeign('wallet_balances_blockchain_currency_foreign');
            $table->dropIndex('wallet_balances_blockchain_currency_foreign');
        });

        /**
         * Drop blockchain_currencies table
         */
        Schema::dropIfExists('blockchain_currencies');

        /**
         * Update currencies table
         */
        DB::statement('TRUNCATE TABLE `currencies`;');
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->string('id')->nullable(false)->first()->primary();
            $table->string('code')->nullable(false)->after('id');
            $table->string('contract_address')->nullable()->after('blockchain');
        });

        /**
         * Create new foreign keys
         */
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->renameColumn('blockchain_currency_code', 'currency_id');
            $table->renameColumn('invoice_currency_code', 'invoice_currency_id');

            $table->foreign('currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('currency_code', 'currency_id');

            $table->foreign('currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->string('currency')->comment('Default payment currency in store. Only fiat.')->change();
            $table->renameColumn('currency', 'currency_id');

            $table->foreign('currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::table('wallet_balances', function (Blueprint $table) {
            $table->renameColumn('blockchain_currency', 'currency_id');

            $table->foreign('currency_id')
                ->references('id')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        /**
         * Drop new foreign keys
         */
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->dropForeign('invoice_addresses_currency_id_foreign');

            $table->renameColumn('currency_id', 'blockchain_currency_code');
            $table->renameColumn('invoice_currency_id', 'invoice_currency_code');

            $table->dropIndex('invoice_addresses_currency_id_foreign');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_currency_id_foreign');

            $table->renameColumn('currency_id', 'currency_code');

            $table->dropIndex('invoices_currency_id_foreign');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign('stores_currency_id_foreign');

            $table->renameColumn('currency_id', 'currency');

            $table->dropIndex('stores_currency_id_foreign');
        });

        Schema::table('wallet_balances', function (Blueprint $table) {
            $table->dropForeign('wallet_balances_currency_id_foreign');

            $table->renameColumn('currency_id', 'blockchain_currency');

            $table->dropIndex('wallet_balances_currency_id_foreign');
        });

        /**
         * Update currencies table
         */
        DB::statement('TRUNCATE TABLE `currencies`;');

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropColumn('code');
            $table->dropColumn('contract_address');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->string('code')->first()->primary();
        });

        /**
         * Create blockchain_currencies table
         */
        Schema::create('blockchain_currencies', function (Blueprint $table) {
            $table->string('blockchain_currency_code')->primary();
            $table->string('currency_code')->nullable(false);

            $table->foreign('currency_code')
                ->references('code')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->enum('blockchain', ['tron', 'bitcoin'])->nullable(false);
            $table->string('contract_address')->nullable(false);
            $table->timestampsTz();
        });

        /**
         * Create old foreign keys
         */
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->foreign('blockchain_currency_code')
                ->references('blockchain_currency_code')->on('blockchain_currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->foreign('currency')
                ->references('code')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::table('wallet_balances', function (Blueprint $table) {
            $table->foreign('blockchain_currency')
                ->references('blockchain_currency_code')->on('blockchain_currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
