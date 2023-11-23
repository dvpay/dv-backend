<?php

namespace App\Console\Commands;

use App\Services\Processing\TransferService;
use Illuminate\Console\Command;

class ExpiredTransferCommand extends Command
{
    protected $signature = 'expired:transfer';

    protected $description = 'Expired Transfer';

    public function handle(TransferService $transferService): void
    {
        $transferService->expiredTransfer(10);
        $transferService->deleteOldTransfer(7);

        $this->info('Transfer success expired');
    }
}
