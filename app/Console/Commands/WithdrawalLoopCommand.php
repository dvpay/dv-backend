<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\TransferStatus;
use App\Enums\WithdrawalInterval;
use App\Exceptions\Processing\QueueException;
use App\Exceptions\Processing\ResourceException;
use App\Models\WithdrawalWallet;
use App\Services\Processing\Contracts\TransferContract;
use App\Services\Processing\TransferService;
use App\Services\WithdrawalWallet\WithdrawalWalletService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class WithdrawalLoopCommand extends Command
{
    protected $signature = 'withdrawal:loop {--blockchain=bitcoin,tron}';

    protected $description = 'Command for loop withdrawal make systemd or supervisor for run him';

    public function handle(
        WithdrawalWalletService $withdrawalWalletService,
        TransferService         $transferService,
        TransferContract        $transferProcessingContract,
    ): void
    {
        while (true) {
            sleep(2);

            $blockchains = explode(',', $this->option('blockchain'));
            WithdrawalWallet::where('withdrawal_enabled', true)
                ->whereNotNull('withdrawal_interval')
                ->where('withdrawal_interval', '!=', WithdrawalInterval::Never->name)
                ->whereIn('blockchain', $blockchains)
                ->with('user')
                ->each(function ($withdrawalWallet) use (
                    $withdrawalWalletService,
                    $transferService,
                    $transferProcessingContract
                ) {
                    if($withdrawalWallet->user->hasPermissionTo('transfer funds')) {
                        $this->info('User disable transfer' . $withdrawalWallet->user->email);
                        return true;
                    }
                    $dto = $withdrawalWalletService->loopWithdrawal($withdrawalWallet);
                    if (empty($dto)) {
                        $this->info("Nothing to withdrawal");
                        $timer = Cache::get('timeNextTransfer');
                        if ($timer <= 0) {
                            Cache::set('timeNextTransfer', time() + 30);
                        }
                        return true;
                    }
                    $this->info("Send processing request to transfer from " . $dto->addressFrom . " -> " . " $dto->addressTo");

                    try {
                        $transferProcessingContract->transferFromAddress($dto);
                        $transferService->createTransfer($dto);
                        Cache::delete('retryTransfer' . $dto->addressFrom);
                    } catch (QueueException $exception) {
                        $this->info($exception->getMessage());
                        Cache::set('timeNextTransfer', time() + 30);
                        return true;
                    } catch (ResourceException $exception) {
                        $this->info($exception->getMessage());
                        Cache::set('timeNextTransfer', time() + 30);
                        sleep(30);
                        return true;
                    } catch (Exception $e) {
                        $transfer = $transferService->createTransfer($dto);
                        $transfer->update(['status' => TransferStatus::Failed->value]);
                        $this->info('Processing error', $e->getMessage());
                        Cache::set('timeNextTransfer', time() + 30);
                        Cache::increment('retryTransfer' . $dto->addressFrom);
                        sleep(30);
                        return true;
                    }

                    $this->info('Processing response true, send next');
                    return true;
                });
        }
    }
}
