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
            $table->char('id', 36)->primary();
            $table->string('slug')->nullable()->unique();
            $table->enum('status', ['waiting', 'waiting_confirmations', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success', 'overpaid'])->nullable();
            $table->char('store_id', 36)->index('invoices_store_id_foreign');
            $table->string('order_id');
            $table->string('currency_id')->index('invoices_currency_id_foreign');
            $table->decimal('amount', 28, 8)->default(0);
            $table->string('description')->nullable();
            $table->string('return_url')->nullable();
            $table->string('success_url')->nullable();
            $table->timestamps();
            $table->timestamp('expired_at');
            $table->string('destination')->nullable();
            $table->text('custom')->nullable()->comment('Column for service information.');
            $table->unsignedBigInteger('attached_by')->nullable();
            $table->timestamp('attached_at')->nullable();
            $table->boolean('is_confirm')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payer_language', 2)->nullable();
            $table->char('payer_id', 36)->nullable()->index('invoices_payer_id_foreign');
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
