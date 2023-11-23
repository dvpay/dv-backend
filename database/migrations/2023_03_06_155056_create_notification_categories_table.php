<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('notification_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();
            $table->string('name', 255);
            $table->tinyInteger('is_active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_categories');
    }
};