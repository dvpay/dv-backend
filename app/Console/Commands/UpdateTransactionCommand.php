<?php

namespace App\Console\Commands;

use App\Models\HotWallet;
use App\Models\Transaction;
use App\Services\Processing\Contracts\TransactionContract;
use Illuminate\Console\Command;

class UpdateTransactionCommand extends Command
{
    protected $signature = 'update:transactions';

    protected $description = 'Command description';

    public function handle(TransactionContract $transactionContract): void
    {
        $this->info('Start update transaction');
        $hotWallets = HotWallet::where('amount', '!=', 0);

        $bar = $this->output->createProgressBar($hotWallets->count());
        $bar->start();

        $hotWallets->each(function ($hotWallet) use ($transactionContract, $bar) {
                $transactions = $transactionContract->getTransactionByAddress($hotWallet->user->processing_owner_id, $hotWallet->address);
                foreach ($transactions as $transaction) {
                    Transaction::where('tx_id', $transaction['txId'])
                        ->where('to_address', $transaction['to'])
                        ->where('from_address', '=', '')
                        ->update([
                            'from_address' => $transaction['from'],
                        ]);
                }
                $bar->advance();
            });

        $bar->finish();
    }
}
