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
        Schema::table('exchange_dictionaries', function (Blueprint $table) {
            $table->foreign(['exchange_id'])->references(['id'])->on('exchanges');
            $table->foreign(['from_currency_id'])->references(['id'])->on('currencies');
            $table->foreign(['to_currency_id'])->references(['id'])->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_dictionaries', function (Blueprint $table) {
            $table->dropForeign('exchange_dictionaries_exchange_id_foreign');
            $table->dropForeign('exchange_dictionaries_from_currency_id_foreign');
            $table->dropForeign('exchange_dictionaries_to_currency_id_foreign');
        });
    }
};
