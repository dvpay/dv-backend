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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', ['waiting', 'paid', 'success', 'expired', 'canceled'])->nullable(false);
            $table->uuid('store_id')->nullable(false);

            $table->foreign('store_id')
                ->references('id')->on('stores')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('order_id')->nullable(false);
            $table->string('currency')->nullable(false);
            $table->decimal('amount', 28, 8)->nullable(false)->default(0.0);
            $table->string('description')->nullable();
            $table->string('return_url')->nullable();
            $table->timestampsTz();
            $table->timestampTz('expired_at')->nullable(false);
        });

        Schema::create('invoice_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id')->nullable(false);

            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('address')->nullable(false);
            $table->enum('blockchain', ['tron', 'bitcoin'])->nullable(false);
        });

        Schema::create('invoice_status_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id')->nullable(false);

            $table->foreign('invoice_id')
                ->references('id')->on('invoices')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->enum('status', ['waiting', 'paid', 'success', 'expired', 'canceled'])->nullable(false);
            $table->enum('previous_status', ['waiting', 'paid', 'success', 'expired', 'canceled'])->nullable(false);
            $table->timestampTz('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_status_history');
        Schema::dropIfExists('invoice_addresses');
        Schema::dropIfExists('invoices');
    }
};
