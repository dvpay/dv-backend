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
            $table->id();
            $table->unsignedBigInteger('service_id')->nullable(false);

            $table->foreign('service_id')
                ->references('id')->on('services')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->char('status', 10)->nullable(false);
            $table->string('message')->nullable(false);
            $table->json('message_variables')->nullable();

            $table->timestampsTz();
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
