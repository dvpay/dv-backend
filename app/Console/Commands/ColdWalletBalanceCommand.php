<?php

namespace App\Console\Commands;

use App\Enums\ExchangeChainType;
use App\Models\ExchangeWithdrawalWallet;
use App\Services\Explorer\Public\Ethereum;
use App\Services\Explorer\Public\Tron;
use Illuminate\Console\Command;

class ColdWalletBalanceCommand extends Command
{
    protected $signature = 'cold:wallet:balance';

    protected $description = 'Command description';

    public function handle(): void
    {
        $tronExplorer = new Tron();
        $ethExplorer = new Ethereum();

        ExchangeWithdrawalWallet::all()
            ->each(function ($address) use ($tronExplorer, $ethExplorer) {
                $chain = ExchangeChainType::tryFrom($address->chain)->blockchain();
                $explorer = match ($chain->name) {
                    'Tron' => $tronExplorer,
                    'Ethereum' => $ethExplorer,
                };
                $address->update(['current_balance' => $explorer->getAddressBalance($address->address)]);
            });
    }
}
