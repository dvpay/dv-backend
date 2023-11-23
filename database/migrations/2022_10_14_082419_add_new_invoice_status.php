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
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'waiting_confirmations', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success'])->nullable(false)->after('id');
        });

        Schema::table('invoice_status_histories', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('previous_status');
        });

        Schema::table('invoice_status_histories', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'waiting_confirmations', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success'])->nullable(false)->after('invoice_id');
            $table->enum('previous_status', ['waiting', 'waiting_confirmations', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success'])->nullable(false)->after('status');
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
            $table->dropColumn('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success'])->nullable(false)->after('id');
        });

        Schema::table('invoice_status_histories', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('previous_status');
        });

        Schema::table('invoice_status_histories', function (Blueprint $table) {
            $table->enum('status', ['waiting', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success'])->nullable(false)->after('invoice_id');
            $table->enum('previous_status', ['waiting', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled', 'success'])->nullable(false)->after('status');
        });
    }
};
