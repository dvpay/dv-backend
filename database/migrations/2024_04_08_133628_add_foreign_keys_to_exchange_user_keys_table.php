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
        Schema::table('exchange_user_keys', function (Blueprint $table) {
            $table->foreign(['key_id'])->references(['id'])->on('exchange_keys');
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
        Schema::table('exchange_user_keys', function (Blueprint $table) {
            $table->dropForeign('exchange_user_keys_key_id_foreign');
            $table->dropForeign('exchange_user_keys_user_id_foreign');
        });
    }
};
