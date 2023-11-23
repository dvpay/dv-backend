<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('invited_id')->nullable();
            $table->foreign('invited_id')->references('id')->on('users');

            $table->string('email');
            $table->string('token')->unique();
            $table->string('role');

            $table->timestamps();

            $table->index('token');

        });
    }

    public function down()
    {
        Schema::dropIfExists('invites');
    }
};