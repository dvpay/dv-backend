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
        Schema::table('exchange_requests', function (Blueprint $table) {
            $table->foreign(['exchange_id'])->references(['id'])->on('exchanges');
            $table->foreign(['user_id'])->references(['id'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_requests', function (Blueprint $table) {
            $table->dropForeign('exchange_requests_exchange_id_foreign');
            $table->dropForeign('exchange_requests_user_id_foreign');
        });
    }
};
