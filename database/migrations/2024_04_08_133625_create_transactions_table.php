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
        Schema::create('transactions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('user_id');
            $table->char('store_id', 36)->nullable()->index();
            $table->char('invoice_id', 36)->nullable()->index();
            $table->string('currency_id');
            $table->string('tx_id', 191)->index();
            $table->enum('type', ['invoice', 'transfer', 'exchange']);
            $table->string('from_address', 191)->nullable();
            $table->string('to_address', 191)->index();
            $table->decimal('amount', 28, 8);
            $table->decimal('amount_usd', 28)->nullable();
            $table->decimal('rate', 28, 8)->nullable();
            $table->decimal('fee', 28, 8);
            $table->boolean('withdrawal_is_manual')->default(false);
            $table->timestamp('network_created_at')->nullable();
            $table->timestamps();
            $table->bigInteger('energy')->nullable();
            $table->bigInteger('bandwidth')->nullable();
            $table->char('payer_id', 36)->nullable()->index('transactions_payer_id_foreign');
            $table->integer('created_at_index')->nullable()->index();

            $table->unique(['tx_id', 'from_address', 'to_address', 'amount']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
