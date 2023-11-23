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
        Schema::create('unconfirmed_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false);

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->uuid('store_id')->nullable(false);

            $table->foreign('store_id')
                ->references('id')->on('stores')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->uuid('invoice_id')->nullable(false);

            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('from_address')->nullable(false);
            $table->string('to_address')->nullable(false);
            $table->string('tx_id')->nullable(false);
            $table->string('currency_id');

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
        Schema::dropIfExists('unconfirmed_transactions');
    }
};
