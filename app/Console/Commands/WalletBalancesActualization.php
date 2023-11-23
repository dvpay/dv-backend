<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Console\Command;

class WalletBalancesActualization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "wallet:balances:actualization {walletId?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualization wallet balances.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $time = time();

        if ($walletId = $this->argument('walletId')) {
            $wallets = [Wallet::where('id', $walletId)->first()];
        } else {
            $wallets = Wallet::all();
        }

        foreach ($wallets as $wallet) {
            foreach ($wallet->balances as $balance) {
                $depositSum = Transaction::select('currency_id, amount')
                    ->where([
                        ['store_id', $wallet->store_id],
                        ['currency_id', $balance->currency_id],
                        ['type', TransactionType::Invoice],
                    ])->sum('amount');

                $withdrawalSum = Transaction::select('currency_id, amount')
                    ->where([
                        ['store_id', $wallet->store_id],
                        ['currency_id', $balance->currency_id],
                        ['type', TransactionType::Transfer],
                    ])->sum('amount');

                $oldBalance = $balance->balance;
                $newBalance = bcsub((string)$depositSum, (string)$withdrawalSum);

                $balance->balance = $newBalance;
                $balance->save();

                $this->info("Wallet id: $wallet->id");
                $this->info("Currency id: $balance->currency_id");
                $this->info("Old balance: $oldBalance");
                $this->info("New balance: $newBalance \n");
            }
        }

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}
