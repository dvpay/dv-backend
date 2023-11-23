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
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->timestampsTz();
        });

        Schema::table('rate_sources', function (Blueprint $table) {
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
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $table->dropTimestampsTz();
        });

        Schema::table('rate_sources', function (Blueprint $table) {
            $table->dropTimestampsTz();
        });
    }
};
