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
            $table->string('site')->nullable()->change();
            $table->integer('invoice_expiration_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            DB::table('stores')->whereNull('site')->update(['site' => '']);
            $table->string('site')->nullable(false)->change();

            DB::table('stores')->whereNull('invoice_expiration_time')->update(['invoice_expiration_time' => 0]);
            $table->integer('invoice_expiration_time')->nullable(false)->change();
        });
    }
};
