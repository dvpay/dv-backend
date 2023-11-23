<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('stores_users', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->string('store_id', 36);
            $table->foreign('store_id')
                ->references('id')
                ->on('stores');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stores_users');
    }
};