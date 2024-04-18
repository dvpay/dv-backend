<?php

namespace App\Console\Commands;

use App\Dto\ProcessingWalletDto;
use App\Enums\Blockchain;
use App\Enums\Metric;
use App\Facades\Prometheus;
use App\Models\User;
use App\Services\Processing\Contracts\ProcessingWalletContract;
use Illuminate\Console\Command;

class ProcessingResourceActualizationCommand extends Command
{
    protected $signature = 'processing:resource:actualization';

    protected $description = 'Actualization processing wallet resources (Tron)';

    public function __construct(private readonly ProcessingWalletContract $processing)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        foreach (User::all() as $user)
        {
            if( empty($user->processing_owner_id)) {
                continue;
            }

            $wallets = $this->processing->getWallets($user->processing_owner_id);

            foreach ($wallets as $wallet) {
                /** @var ProcessingWalletDto $wallet */
                if(Blockchain::Tron->value === $wallet->blockchain) {

                    Prometheus::gaugeSet(
                        Metric::BackendTronProcessingWalletEnergy->getName(),
                        $wallet->energy,
                        [$user->processing_owner_id]
                    );

                    Prometheus::gaugeSet(
                        Metric::BackendTronProcessingWalletEnergyLimit->getName(),
                        $wallet->energyLimit,
                        [$user->processing_owner_id]
                    );

                    Prometheus::gaugeSet(
                        Metric::BackendTronProcessingWalletBandwidth->getName(),
                        $wallet->bandwidth,
                        [$user->processing_owner_id]
                    );

                    Prometheus::gaugeSet(
                        Metric::BackendTronProcessingWalletBandwidthLimit->getName(),
                        $wallet->bandwidthLimit,
                        [$user->processing_owner_id]
                    );
                }

            }

        }
    }
}
