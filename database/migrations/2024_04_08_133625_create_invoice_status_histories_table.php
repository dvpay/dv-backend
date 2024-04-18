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
        Schema::create('invoice_status_histories', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('invoice_id', 36)->index('invoice_status_history_invoice_id_foreign');
            $table->enum('status', ['waiting', 'waiting_confirmations', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success', 'overpaid'])->nullable();
            $table->enum('previous_status', ['waiting', 'waiting_confirmations', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success', 'overpaid'])->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_status_histories');
    }
};
