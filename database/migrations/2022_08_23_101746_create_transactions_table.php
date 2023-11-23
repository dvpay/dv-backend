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
            $table->unsignedBigInteger('user_id')->nullable(false);

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->uuid('invoice_id')->nullable();

            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->enum('blockchain', ['tron', 'bitcoin'])->nullable(false);
            $table->string('tx_id')->nullable(false);
            $table->enum('type', ['invoice', 'transfer', 'exchange'])->nullable(false);
            $table->string('from_address')->nullable(false);
            $table->string('to_address')->nullable(false);
            $table->decimal('amount', 28, 8)->nullable(false);
            $table->decimal('fee', 28, 8)->nullable(false);
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
