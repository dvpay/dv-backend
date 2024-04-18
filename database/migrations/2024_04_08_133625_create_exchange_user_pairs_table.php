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
        Schema::create('exchange_user_pairs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exchange_id')->index('exchange_user_pairs_exchange_id_foreign');
            $table->unsignedBigInteger('user_id')->index('exchange_user_pairs_user_id_foreign');
            $table->string('currency_from');
            $table->string('currency_to');
            $table->string('symbol');
            $table->softDeletes();
            $table->timestamps();
            $table->string('type')->default('sell');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_user_pairs');
    }
};
