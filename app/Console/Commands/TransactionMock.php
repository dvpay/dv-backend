<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\CreateInvoiceDto;
use App\Enums\CurrencySymbol;
use App\Enums\HttpMethod;
use App\Enums\InvoiceStatus;
use App\Exceptions\ProcessingException;
use App\Models\InvoiceAddress;
use App\Models\Store;
use App\Services\Invoice\InvoiceCreator;
use App\Services\Processing\Contracts\Client;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TransactionMock extends Command
{
    public function __construct(
        private readonly InvoiceCreator $invoiceCreator,
        private readonly Client $client
    )
    {
        parent::__construct();


    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "transaction:mock {storeId}, {txId}, {invoiceCount=2}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Created test invoices for set transaction.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        if (
            app()->environment() != 'local'
            && app()->environment() != 'stage'
        ) {
            $this->info('Forbidden for prod.');

            return;
        }

        $time = time();

        $storeId = $this->argument('storeId');
        $invoiceCount = $this->argument('invoiceCount');
        $txId = $this->argument('txId');

        $store = Store::find($storeId);

        $watches = [];

        for ($count = 0; $count < $invoiceCount; $count++) {
            $dto = new CreateInvoiceDto([
                'status' => InvoiceStatus::Waiting,
                'orderId' => 'test_' . fake()->uuid,
                'currencyId' => CurrencySymbol::USD->value,
                'amount' => 100,
            ]);

            $invoice = $this->invoiceCreator->store($dto, $store);
            foreach ($invoice->addresses as $address) {
                $watchId = fake()->uuid;

                $address->watch_id = $watchId;
                $address->save();

                $watches[] = $watchId;
            }
        }

        $data = [
            'tx' => $txId,
            'owner' => $store->user->processing_owner_id,
            'watchIds' => $watches,
            'save' => false,
        ];
        $res = $this->client->request(HttpMethod::POST, '/mock-data/transaction', $data);
        if ($res->getStatusCode() !== Response::HTTP_CREATED) {
            $this->info(json_encode($data));
            throw new ProcessingException(__('Failed on processing'), $res);
        }

        $response = json_decode((string) $res->getBody(), true);
        foreach ($response['watches'] as $watch) {
            $invoiceAddress = InvoiceAddress::where('watch_id', $watch['Id'])->first();

            $invoiceAddress->address = $watch['Address'];
            $invoiceAddress->save();
        }

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}
