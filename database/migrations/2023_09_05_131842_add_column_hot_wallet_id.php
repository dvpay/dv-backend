<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('hot_wallets', function (Blueprint $table) {
            $table->decimal('amount', 28, 8)->default(0);
            $table->decimal('amount_usd', 28, 8)->default(0);
        });

        DB::unprepared('
            CREATE TRIGGER hot_wallets_sum_transaction
                AFTER INSERT
                ON transactions FOR EACH ROW
            BEGIN
            
                DECLARE sum_transfer decimal(28,8);
                DECLARE sum_payment decimal(28,8);
                DECLARE sum_payment_usd decimal(28,8);
                DECLARE sum_transfer_usd decimal(28,8);

                DECLARE hotWalletAddress varchar(64);
            
                IF NEW.type = "transfer" THEN
                    SET hotWalletAddress = NEW.from_address;
                ELSE
                    SET hotWalletAddress = NEW.to_address;
                END IF;
            
                SELECT COALESCE(sum(amount), 0), COALESCE(sum(amount_usd), 0) into sum_transfer, sum_transfer_usd from transactions where from_address = hotWalletAddress;
                SELECT COALESCE(sum(amount), 0), COALESCE(sum(amount_usd), 0) into sum_payment, sum_payment_usd from transactions where to_address = hotWalletAddress;
            
                UPDATE hot_wallets
                SET amount = sum_payment - sum_transfer, 
                amount_usd = sum_payment_usd - sum_transfer_usd
                WHERE address = hotWalletAddress;
            
            END;
        ');
    }


    public function down(): void
    {
        Schema::table('hot_wallets', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('amount_usd');
        });

        DB::unprepared('DROP TRIGGER `hot_wallets_sum_transaction`');
    }
};
