<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Models\WalletBalance;
use Illuminate\Console\Command;

class WalletBalancesTrxDrop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "wallet:balances:trx:drop";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop TRX wallet balances.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $time = time();

        WalletBalance::where('currency_id', CurrencySymbol::TRX->name . '.' . Blockchain::Tron->name)->delete();

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}
