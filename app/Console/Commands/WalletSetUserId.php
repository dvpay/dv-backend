<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Wallet;
use Illuminate\Console\Command;

class WalletSetUserId extends Command
{
    protected $signature = 'wallet:set:user:id';

    public function handle()
    {
        $time = time();

        $wallets = Wallet::where('user_id', null)->get();
        foreach ($wallets as $wallet) {
            if (!$store = $wallet->store) {
                continue;
            }

            $wallet->user_id = $store->user_id;
            $wallet->saveOrFail();
        }

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}
