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
            $table->uuid('id')->primary();
            $table->string('user_id');
            $table->string('invoice_id');
            $table->string('currency_id');
            $table->string('tx_id');
            $table->enum('type', ['invoice', 'transfer', 'exchange']);
            $table->string('from_address');
            $table->string('to_address');
            $table->decimal('amount', 28, 8);
            $table->decimal('fee', 28, 8);
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
        Schema::dropIfExists('transactions');
    }
};
