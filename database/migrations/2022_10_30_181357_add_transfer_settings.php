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
        Schema::table("transactions", function (Blueprint $table) {
            $table->uuid('invoice_id')->nullable()->index()->change();
            $table->index('tx_id');
        });

        Schema::table("wallets", function (Blueprint $table) {
            $table->decimal('withdrawal_min_balance', 28, 8)->nullable()->default(0);
            $table->unsignedInteger('withdrawal_interval')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("transactions", function (Blueprint $table) {
            DB::table('transactions')
                ->whereNull('invoice_id')
                ->update(['invoice_id' => '00000000-0000-0000-0000-000000000000']);

            $table->string('invoice_id')->nullable(false)->change();
            $table->dropIndex(['tx_id']);
            $table->dropIndex(['invoice_id']);
        });

        Schema::table("wallets", function (Blueprint $table) {
            $table->dropColumn(['withdrawal_min_balance', 'withdrawal_interval']);
        });
    }
};
