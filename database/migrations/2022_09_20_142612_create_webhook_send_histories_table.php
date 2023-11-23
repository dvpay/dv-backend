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
        Schema::create('webhook_send_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_id')->nullable(false);
            $table->enum('type', ['InvoiceCreated', 'PaymentReceived', 'InvoiceExpired'])->nullable(false);
            $table->string('url')->nullable(false);
            $table->enum('status', ['success', 'fail'])->nullable(false);
            $table->json('request')->nullable(false);
            $table->json('response')->nullable(false);
            $table->mediumInteger('response_status_code')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhook_send_histories');
    }
};
