<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\WithdrawalInterval;
use App\Models\WithdrawalWallet;
use App\Services\Processing\Contracts\TransferContract;
use App\Services\Processing\TransferService;
use App\Services\WithdrawalWallet\WithdrawalWalletService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WithdrawalLoopCommand extends Command
{
    protected $signature = 'withdrawal:loop';

    protected $description = 'Command for loop withdrawal make systemd or supervisor for run him';

    public function handle(
        WithdrawalWalletService $withdrawalWalletService,
        TransferService         $transferService,
        TransferContract        $transferProcessingContract,
    ): void
    {
        while (true) {
            sleep(2);

            WithdrawalWallet::where('withdrawal_enabled', true)
                ->whereNotNull('withdrawal_interval')
                ->where('withdrawal_interval', '!=', WithdrawalInterval::Never->name)
                ->with('user')
                ->each(function ($withdrawalWallet) use (
                    $withdrawalWalletService,
                    $transferService,
                    $transferProcessingContract
                ) {
                    $dto = $withdrawalWalletService->loopWithdrawal($withdrawalWallet);
                    if (empty($dto)) {
                        $this->info("Nothing to withdrawal");
                        return true;
                    }
                    $this->info("Send processing request to transfer from " . $dto->addressFrom . " -> " . " $dto->addressTo");

                    try {
                        $transferProcessingContract->transferFromAddress($dto);
                        Cache::delete('retryTransfer' . $dto->addressFrom);

                    } catch (Exception $e) {
                        $this->info('Processing error', $e->getMessage());
                        Cache::set('timeNextTransfer', time() + 30);
                        Cache::increment('retryTransfer' . $dto->addressFrom);
                        sleep(30);
                        return true;
                    }

                    $this->info('Processing response true, send next');
                    $transferService->createTransfer($dto);

                    return true;
                });
        }
    }
}
