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
        Schema::table('notification_user_targets', function (Blueprint $table) {
            $table->foreign(['notification_target_id'])->references(['id'])->on('notification_targets')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_user_targets', function (Blueprint $table) {
            $table->dropForeign('notification_user_targets_notification_target_id_foreign');
            $table->dropForeign('notification_user_targets_user_id_foreign');
        });
    }
};
