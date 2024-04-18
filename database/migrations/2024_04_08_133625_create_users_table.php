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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('language_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('is_admin')->default(false);
            $table->char('processing_owner_id', 36)->nullable()->unique()->comment('processing owner\'s id');
            $table->string('location', 50)->nullable();
            $table->char('language', 2)->default('en');
            $table->string('rate_source')->default('Binance')->index('users_rate_source_foreign');
            $table->string('phone', 32)->nullable();
            $table->string('google2fa_secret')->nullable();
            $table->boolean('google2fa_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
