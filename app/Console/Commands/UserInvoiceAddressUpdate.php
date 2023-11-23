<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\RateSource;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Models\User;
use App\Models\UserInvoiceAddress;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\Contracts\AddressContract;
use Exception;
use Illuminate\Console\Command;
use Throwable;

/**
 * UserInvoiceAddressUpdate
 */
class UserInvoiceAddressUpdate extends Command
{

    /**
     * @var ServiceLogLaunch
     */
    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

    /**
     * @param AddressContract $addressContract
     * @param CurrencyRateService $currencyRateService
     */
    public function __construct(
        private readonly AddressContract     $addressContract,
        private readonly CurrencyRateService $currencyRateService
    )
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "user:invoice:address:update {ownerId?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set currency rates from rate sources.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->initMonitor();
        
        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start User Invoice Update Address status',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        try {
            $time = time();

            if ($ownerId = $this->argument('ownerId')) {
                $this->updateAddresses($ownerId);
            } else {
                $owners = User::where('processing_owner_id', '!=', null)->get();
                foreach ($owners as $owner) {
                    $this->updateAddresses($owner->processing_owner_id);
                }
            }

            $this->info('The command was successful! ' . time() - $time . ' s.');

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'The command was successful!',
                serviceLogLaunch: $this->serviceLogLaunch,
            );
        } catch (Throwable $e) {
            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Down,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    private function updateAddresses(string $ownerId)
    {
        $addresses = $this->addressContract->getAll($ownerId);

        $userInvoiceAddressIds = [];

        foreach ($addresses as $address) {
            $userAddress = UserInvoiceAddress::where([
                ['processing_owner_id', $ownerId],
                ['address', $address['address']],
            ])->first();

            $blockchain = Blockchain::tryFrom($address['blockchain']);
            $balance = number_format($address['balance'], 8, '.', '');

            if ($userAddress) {
                $userAddress->state = $address['state'];
                $userAddress->balance = (float)$balance;
                $userAddress->balance_usd = (float)$this->inUsd($blockchain, (string)$balance);
                $userAddress->watch_id = $address['watch'] ? $address['watch']['id'] : null;
            } else {
                $userAddress = new UserInvoiceAddress([
                    'state'               => $address['state'],
                    'processing_owner_id' => $ownerId,
                    'blockchain'          => $blockchain,
                    'address'             => $address['address'],
                    'watch_id'            => $address['watch'] ? $address['watch']['id'] : null,
                    'balance'             => (float)$balance,
                    'balance_usd'         => $this->inUsd($blockchain, (string)$balance),
                    'currency_id'         => $this->getCurrencyId($blockchain),
                ]);
            }
            $userAddress->saveOrFail();

            $userInvoiceAddressIds[] = $userAddress->id;
        }

        UserInvoiceAddress::where('processing_owner_id', $ownerId)->whereNotIn('id', $userInvoiceAddressIds)->delete();
    }

    /**
     * @param Blockchain $blockchain
     * @return string
     * @throws Exception
     */
    private function getCurrencyId(Blockchain $blockchain): string
    {
        switch ($blockchain) {
            case Blockchain::Tron:
                $result = CurrencySymbol::USDT->value . '.' . $blockchain->name;
                break;

            case Blockchain::Bitcoin:
                $result = CurrencySymbol::BTC->value . '.' . $blockchain->name;
                break;

            default:
                throw new Exception('Undefined blockchain.');
        }

        return $result;
    }

    /**
     * @param Blockchain $blockchain
     * @param string $balance
     * @return string
     * @throws Exception
     */
    private function inUsd(Blockchain $blockchain, string $balance): string
    {
        switch ($blockchain) {
            case Blockchain::Tron:
                $from = CurrencySymbol::USDT;
                break;

            case Blockchain::Bitcoin:
                $from = CurrencySymbol::BTC;
                break;

            default:
                throw new Exception('Undefined blockchain.');
        }

        return $this->currencyRateService->inUsd(
            RateSource::Binance,
            $from,
            CurrencySymbol::USDT,
            $balance,
            true
        );
    }

    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronUserInvoiceAddressUpdate)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);
    }
}