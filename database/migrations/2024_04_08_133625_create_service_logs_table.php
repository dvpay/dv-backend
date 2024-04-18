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
        Schema::create('service_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message');
            $table->json('message_variables')->nullable();
            $table->timestamps();
            $table->string('log_id', 36)->nullable()->index();
            $table->bigInteger('memory')->nullable();
            $table->unsignedBigInteger('service_log_launch_id')->nullable()->index('service_logs_service_log_launch_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_logs');
    }
};
