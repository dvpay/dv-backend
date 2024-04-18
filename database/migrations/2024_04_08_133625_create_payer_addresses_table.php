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
        Schema::create('payer_addresses', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('payer_id', 36)->index('payer_addresses_payer_id_foreign');
            $table->string('currency_id')->index('payer_addresses_currency_id_foreign');
            $table->enum('blockchain', ['tron', 'bitcoin', 'ethereum']);
            $table->string('address');
            $table->softDeletes();
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
        Schema::dropIfExists('payer_addresses');
    }
};
