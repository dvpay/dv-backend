<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\InvoiceStatus;
use App\Enums\RateSource;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Currency\CurrencyRateService;
use App\Services\Exchange\ExchangeService;
use DateTime;
use Illuminate\Console\Command;
use Throwable;

class InvoiceUpdateStatus extends Command
{

    private ServiceLogLaunch $serviceLogLaunch;
    private Service $service;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "invoice:update:status";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update invoice status on expired.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Throwable
     */

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->initMonitor();
        try {
            $time = time();
            // todo change foreach to each generator
            $invoices = Invoice::where([
                ['status', InvoiceStatus::Waiting],
                ['expired_at', '<', new DateTime()],
            ])->get();

            foreach ($invoices as $invoice) {
                $invoice->updateStatus(InvoiceStatus::Expired);
            }

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Invoice status success update',
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            $this->info('The command was successful! ' . time() - $time . ' s.');
        } catch (Throwable $e) {

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            throw $e;
        }
    }

    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronInvoiceUpdateStatus)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);

    }
}