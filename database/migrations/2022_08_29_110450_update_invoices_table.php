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
            $table->enum('status', ['waiting', 'paid', 'partially_paid', 'partially_paid_expired', 'expired', 'canceled'])->nullable(false)->after('id');
            $table->renameColumn('currency', 'currency_code');
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
            $table->enum('status', ['waiting', 'paid', 'success', 'expired', 'canceled'])->nullable(false)->after('id');
            $table->renameColumn('currency_code', 'currency');
        });
    }
};
