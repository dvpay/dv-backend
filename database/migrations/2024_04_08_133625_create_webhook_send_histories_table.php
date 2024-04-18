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
            $table->char('id', 36)->primary();
            $table->string('invoice_id');
            $table->string('type', 64);
            $table->string('url');
            $table->enum('status', ['success', 'fail']);
            $table->json('request');
            $table->json('response');
            $table->mediumInteger('response_status_code');
            $table->timestamps();
            $table->string('tx_hash')->nullable();
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
