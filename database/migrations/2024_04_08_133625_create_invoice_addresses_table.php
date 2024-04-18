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
        Schema::create('invoice_addresses', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('invoice_id', 36)->index('invoice_addresses_invoice_id_foreign');
            $table->string('address');
            $table->enum('blockchain', ['tron', 'bitcoin', 'ethereum'])->nullable();
            $table->timestamps();
            $table->string('currency_id')->default('BTC')->index('invoice_addresses_currency_id_foreign');
            $table->decimal('balance', 28, 8)->default(0);
            $table->decimal('rate', 28, 8);
            $table->string('invoice_currency_id');
            $table->timestamp('exchange_rate_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_addresses');
    }
};
