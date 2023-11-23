<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('notification_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained();
            $table->foreignId('notification_id')->constrained();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_user');
    }
};