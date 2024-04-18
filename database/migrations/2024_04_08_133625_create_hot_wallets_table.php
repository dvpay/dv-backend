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
        Schema::create('hot_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('currency_id');
            $table->unsignedBigInteger('user_id');
            $table->string('address', 64);
            $table->enum('blockchain', ['tron', 'bitcoin', 'ethereum']);
            $table->timestamps();
            $table->decimal('amount', 28, 8)->default(0);
            $table->decimal('amount_usd', 28, 8)->default(0);

            $table->unique(['currency_id', 'address']);
            $table->index(['user_id', 'address']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hot_wallets');
    }
};
