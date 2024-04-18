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
        Schema::table('service_log_launches', function (Blueprint $table) {
            $table->foreign(['service_id'])->references(['id'])->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_log_launches', function (Blueprint $table) {
            $table->dropForeign('service_log_launches_service_id_foreign');
        });
    }
};
