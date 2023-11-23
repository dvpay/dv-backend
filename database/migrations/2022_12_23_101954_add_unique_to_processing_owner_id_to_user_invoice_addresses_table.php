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
        Schema::table('user_invoice_addresses', function (Blueprint $table) {
            $table->unique(['processing_owner_id', 'address']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_invoice_addresses', function (Blueprint $table) {
            $table->dropForeign(['processing_owner_id']);

            $table->dropUnique(['processing_owner_id', 'address']);

            $table->foreign('processing_owner_id')
                ->references('processing_owner_id')->on('users')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });
    }
};
