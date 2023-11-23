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
        Schema::table('stores', function (Blueprint $table) {
            $table->string('success_url', 255)->after('return_url')->nullable();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('success_url', 255)->after('return_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('success_url');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('success_url');
        });
    }
};
