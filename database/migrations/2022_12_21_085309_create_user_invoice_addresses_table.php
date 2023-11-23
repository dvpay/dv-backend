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
        Schema::create('user_invoice_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('state')->nullable(false);
            $table->string('processing_owner_id')->nullable(false);

            $table->foreign('processing_owner_id')
                ->references('processing_owner_id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('blockchain')->nullable(false);
            $table->string('address')->nullable(false);
            $table->string('watch_id')->nullable();
            $table->decimal('balance', 28, 8)->nullable(false)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_invoice_addresses');
    }
};
