<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\InvoiceAddressBalanceActualization as AddressBalanceJob;
use Illuminate\Console\Command;

class InvoiceAddressBalanceActualization extends Command
{
    protected $signature = 'invoice:balance:actualization {ownerId}';

    public function handle()
    {
        $time = time();

        $ownerId = $this->argument('ownerId');

        AddressBalanceJob::dispatchSync($ownerId);

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}
