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
        Schema::table('wallets', function (Blueprint $table) {
            $table->boolean('enable_automatic_exchange')->default(false);
            $table->unsignedBigInteger('exchange_id')->nullable();

            $table->foreign('exchange_id')
                ->references('id')->on('exchanges')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['exchange_id']);

            $table->dropColumn('enable_automatic_exchange');
            $table->dropColumn('exchange_id');
        });
    }
};
