<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //$table->dropIndex('transactions_tx_id_index');
            $table->dropUnique('transactions_tx_id_unique');
            $table->string('from_address')->nullable()->change();
            $table->unique(['tx_id', 'from_address', 'to_address'], 'transactions_tx_id_from_address_to_address_unique');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_tx_id_from_address_to_address_unique');
        });
    }
};
