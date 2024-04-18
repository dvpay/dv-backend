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
        Schema::create('exchange_dictionaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exchange_id')->nullable()->index('exchange_dictionaries_exchange_id_foreign');
            $table->string('from_currency_id')->index('exchange_dictionaries_from_currency_id_foreign');
            $table->string('to_currency_id')->index('exchange_dictionaries_to_currency_id_foreign');
            $table->decimal('min_quantity')->default(0);
            $table->integer('decimals')->nullable();
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
        Schema::dropIfExists('exchange_dictionaries');
    }
};
