<?php

namespace App\Console\Commands;

use App\Enums\TransactionType;
use App\Models\HotWallet;
use App\Models\Transaction;
use App\Services\Processing\Contracts\TransferContract;
use Exception;
use Illuminate\Console\Command;

class ResendProcessingCallbackCommand extends Command
{
    protected $signature = 'resend:processing:callback';

    protected $description = 'Command description';

    public function handle(
        TransferContract $transferContract
    ): void
    {

       /* HotWallet::where('amount', '!=', '0')
            ->each(
                callback: function ($wallet) use ($transferContract) {
                    $user = $wallet->user;
                    try {
                        $transferContract->syncTransactions(
                            owner: $user->processing_owner_id,
                            address: $wallet->address,
                            transactions: $wallet->allTransactions->pluck('tx_id')->toArray(),
                            blockchain: $wallet->blockchain);
                        $this->info("success send");
                    } catch (Exception $exception) {
                        $this->info($exception->getMessage());
                        $this->info($wallet->address);
                    }
                }
            );*/
        HotWallet::whereIn('address', [])
            ->each(
                callback: function ($wallet) {
                    $sumTransferAmount = Transaction::query()
                        ->where(['from_address' => $wallet->adress, 'type' => 'transfer'])
                        ->sum('amount') ?? 0;

                    $sumTransferAmountUsd = Transaction::query()
                        ->where(['from_address' => $wallet->adress, 'type' => 'transfer'])
                        ->sum('amount_usd') ?? 0;

                    $sumPaymentAmount = Transaction::query()
                        ->where(['from_address' => $wallet->adress, 'type' => 'deposit'])
                        ->sum('amount') ?? 0;
                    $sumPaymentAmountUsd = Transaction::query()
                        ->where(['from_address' => $wallet->adress, 'type' => 'deposit'])
                        ->sum('amount_usd') ?? 0;

                    $wallet->update([
                        'amount'     => $sumPaymentAmount - $sumTransferAmount,
                        'amount_usd' => $sumPaymentAmountUsd - $sumTransferAmountUsd,
                    ]);
                });

    }
}
