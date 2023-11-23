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
        Schema::create('exchange_user_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(false);

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->unsignedBigInteger('key_id')->nullable(false);

            $table->foreign('key_id')
                ->references('id')->on('exchange_keys')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('value')->nullable(false);

            $table->timestampsTz();

            $table->unique(['user_id', 'key_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_user_keys');
    }
};
