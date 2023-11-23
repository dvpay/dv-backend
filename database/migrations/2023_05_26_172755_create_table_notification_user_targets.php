<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_user_targets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('notification_target_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('notification_target_id')
                ->references('id')
                ->on('notification_targets')
                ->onDelete('cascade');

            $table->primary(['user_id', 'notification_target_id'], 'user_has_notification_targets_notification_target_id_user_id_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_user_targets');
    }
};
