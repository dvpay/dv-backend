<?php

namespace App\Jobs;

use App\Dto\CreateInvoiceDto;
use App\Dto\HotWallet\HotWalletDto;
use App\Dto\ProcessingCallbackDto;
use App\Enums\Blockchain;
use App\Enums\CurrencyId;
use App\Enums\HotWalletState;
use App\Enums\InvoiceStatus;
use App\Enums\RateSource;
use App\Enums\TransactionType;
use App\Exceptions\RateNotFoundException;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\PayerAddress;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use App\Services\HotWallet\HotWalletServiceInterface;
use App\Services\Invoice\InvoiceAddressCreator;
use App\Services\Invoice\InvoiceCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

//TODO remove if callback work normal
class PaymentCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ProcessingCallbackDto $dto,
        private readonly PayerAddress          $payerAddress,
    )
    {
    }

    public function handle(
        string                    $minTransactionConfirmations,
        InvoiceCreator            $invoiceCreator,
        InvoiceAddressCreator     $invoiceAddressCreator,
        CurrencyRateService       $currencyRateService,
        CurrencyConversion        $currencyConversion,
        HotWalletServiceInterface $hotWalletService,
    ): void
    {
        Log::channel('processingLog')->info('[dto]', (array)$this->dto);

        try {
            DB::beginTransaction();

            $transactionExists = Transaction::where('currency_id', $this->payerAddress->currency_id)
                ->where('tx_id', $this->dto->tx)
                ->where('to_address', $this->dto->address)
                ->exists();

            if ($transactionExists) {
                DB::commit();
                return;
            }

            $payer = $this->payerAddress->payer;
            $store = $payer->store;


            $currency = Currency::where('id', $this->payerAddress->currency_id)->first();

            $amount = $currencyConversion->convert(
                amount: $this->dto->amount,
                rate: $this->rateCalculation($store, $currency, $currencyRateService),
            );

            $invoiceDto = new CreateInvoiceDto([
                'status'      => InvoiceStatus::Waiting,
                'orderId'     => '',
                'amount'      => $amount,
                'currencyId'  => $store->currency->id,
                'destination' => null,
                'payer'       => $payer,
            ]);

            $invoice = $invoiceCreator->store($invoiceDto, $store);
            $invoiceAddressCreator->updateInvoiceStaticAddress($invoice, $this->payerAddress);

            if ($this->checkConfirmation($minTransactionConfirmations)) {
                $this->createUnconfirmedTransaction($this->dto, $store, $invoice);
                $invoice->updateStatus(InvoiceStatus::WaitingConfirmations);
                DB::commit();
                return;
            }
            $this->createHotWallet($hotWalletService, $store, $this->dto->address, $currency->blockchain);

            if ($this->createTransaction($this->dto, $store, $invoice) && $this->dto->status === InvoiceStatus::Paid) {
                $invoice->updateStatus($this->dto->status);
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    private function checkConfirmation(string $minTransactionConfirmations): bool
    {
        if ($this->dto->status == InvoiceStatus::Expired || $this->dto->confirmations < $minTransactionConfirmations) {
            return true;
        }
        return false;
    }

    private function createUnconfirmedTransaction(ProcessingCallbackDto $dto, Store $store, Invoice $invoice): void
    {
        UnconfirmedTransaction::firstOrCreate([
            'currency_id' => $this->payerAddress->currency_id,
            'tx_id'       => $dto->tx,
        ], [
            'user_id'      => $store->user_id,
            'store_id'     => $store->id,
            'invoice_id'   => $invoice->id,
            'from_address' => $dto->sender ?? '',
            'to_address'   => $dto->address,
            'tx_id'        => $dto->tx,
            'currency_id'  => $this->payerAddress->currency_id
        ]);
    }

    /**
     * @throws Throwable
     */
    private function createTransaction(
        ProcessingCallbackDto $dto,
        Store                 $store,
        Invoice               $invoice
    ): bool
    {
        $invoiceAddress = $invoice->addresses()->where('address', $this->payerAddress->address)
            ->firstOrFail();

        $transaction = new Transaction([
            'store_id'           => $store->id,
            'user_id'            => $store->user_id,
            'invoice_id'         => $invoice->id,
            'currency_id'        => $this->payerAddress->currency_id,
            'tx_id'              => $dto->tx,
            'type'               => TransactionType::Invoice,
            'from_address'       => $dto->sender ?? null,
            'to_address'         => $dto->address,
            'amount'             => $dto->amount,
            'amount_usd'         => $dto->amount / $invoiceAddress->rate,
            'rate'               => $invoiceAddress->rate,
            'fee'                => 0,
            'network_created_at' => $dto->time ? date('Y-m-d H:i:s', strtotime($dto->time)) : null,
        ]);
        return $transaction->saveOrFail();
    }

    private function rateCalculation(Store $store, Currency $currency, CurrencyRateService $currencyRateService)
    {
        $rateSource = RateSource::fromStore($store);

        $data = $currencyRateService->getCurrencyRate(
            $rateSource,
            $store->currency->code,
            $currency->code,
        );

        if (!$data) {
            throw new RateNotFoundException();
        }

        if ($currency->blockchain == Blockchain::Bitcoin) {
            $scale = bcmul($data['rate'], bcdiv($store->rate_scale, '100'));
            $data['rate'] = bcadd($data['rate'], $scale);
        }

        return $data['rate'];

    }

    private function createHotWallet(HotWalletServiceInterface $hotWalletService, Store $store, string $address, Blockchain $blockchain): void
    {
        $dto = new HotWalletDto([
            'currencyId' => CurrencyId::tryFrom($this->payerAddress->currency_id),
            'user'       => $store->user,
            'address'    => $address,
            'blockchain' => $blockchain
        ]);

        $hotWalletService->storeHotWallet($dto);
    }


}
