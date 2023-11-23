<?php

namespace App\Console\Commands;

use App\Models\HotWallet;
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
        HotWallet::where('amount', '<', 0)->each(
            callback: function ($wallet) use ($transferContract) {
                $user = $wallet->user;
                try {
                    $transferContract->resendCallback(owner: $user->processing_owner_id, address: $wallet->address);
                } catch (Exception $exception) {
                    $this->info($exception->getMessage());
                }
            }
        );
    }
}
