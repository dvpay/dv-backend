<?php

namespace App\Services\Processing\CallbackHandlers;

use App\Dto\CreateInvoiceDto;
use App\Dto\HotWallet\HotWalletDto;
use App\Dto\ProcessingCallbackDto;
use App\Enums\Blockchain;
use App\Enums\CurrencyId;
use App\Enums\HotWalletState;
use App\Enums\InvoiceStatus;
use App\Enums\RateSource;
use App\Enums\TransactionType;
use App\Events\PaymentReceivedEvent;
use App\Events\UnconfirmedTransactionCreatedEvent;
use App\Exceptions\LowConfirmationException;
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
use App\Services\Processing\Contracts\CallbackHandlerContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentCallback implements CallbackHandlerContract
{

    public function __construct(
        private readonly CurrencyConversion        $currencyConversion,
        private readonly CurrencyRateService       $currencyRateService,
        private readonly InvoiceCreator            $invoiceCreator,
        private readonly InvoiceAddressCreator     $invoiceAddressCreator,
        private readonly string                    $minTransactionConfirmations,
        private readonly HotWalletServiceInterface $hotWalletService,
    )
    {

    }

    /**
     * @throws Throwable
     */
    public function handle(ProcessingCallbackDto $dto): void
    {
        $payerAddress = PayerAddress::where([
            ['blockchain', $dto->blockchain],
            ['payer_id', $dto->payer_id],
            ['address', $dto->address]
        ])->firstOrFail();

        Log::channel('processingLog')->info('[dto]', (array)$dto);

        try {
            DB::beginTransaction();
            $transactionExists = Transaction::where('currency_id', $payerAddress->currency_id)
                ->where('tx_id', $dto->tx)
                ->where('to_address', $dto->address)
                ->where('amount', $dto->amount)
                ->exists();

            if ($transactionExists) {
                DB::rollBack();
                return;
            }

            $payer = $payerAddress->payer;
            $store = $payer->store;

            $currency = Currency::where('id', $payerAddress->currency_id)->first();
            $rate = $this->rateCalculation($store, $currency, $this->currencyRateService);
            $amount = $this->currencyConversion->convert(
                amount: $dto->amount,
                rate: $rate,
            );

            if ($this->checkConfirmation($dto)) {
                $this->createUnconfirmedTransaction($dto, $store, $payerAddress, $amount);
                DB::commit();
                throw new LowConfirmationException(__('Low Confirmations'), 422);
            }

            $this->createHotWallet($store, $dto->address, $currency->blockchain, $payerAddress);

            $invoiceDto = new CreateInvoiceDto([
                'status'      => InvoiceStatus::Paid,
                'orderId'     => '',
                'amount'      => $amount,
                'currencyId'  => $store->currency->id,
                'destination' => null,
                'payer'       => $payer,
            ]);

            $invoice = $this->invoiceCreator->store($invoiceDto, $store);
            $this->invoiceAddressCreator->updateInvoiceStaticAddress($invoice, $payerAddress);

            if (($transaction = $this->createTransaction($dto, $store, $payerAddress, $invoice, $amount, $rate)) && $dto->status === InvoiceStatus::Paid) {
                $invoice->updateStatus(InvoiceStatus::Paid);
                event(new PaymentReceivedEvent($transaction));
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }

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

    private function createUnconfirmedTransaction(
        ProcessingCallbackDto $dto,
        Store                 $store,
        PayerAddress          $payerAddress,
        string                $amount): void
    {
        $existingTransaction = UnconfirmedTransaction::where('currency_id', $payerAddress->currency_id)
            ->where('tx_id', $dto->tx)
            ->first();

        if (!$existingTransaction) {
            $unconfirmedTransactions = UnconfirmedTransaction::create([
                'user_id'      => $store->user_id,
                'store_id'     => $store->id,
                'invoice_id'   => null,
                'from_address' => $dto->sender ?? '',
                'to_address'   => $dto->address,
                'tx_id'        => $dto->tx,
                'amount'       => $dto->amount,
                'amount_usd'   => $amount,
                'payer_id'     => $payerAddress->payer_id,
                'currency_id'  => $payerAddress->currency_id
            ]);
            event(new UnconfirmedTransactionCreatedEvent($unconfirmedTransactions));
        }
    }

    private function checkConfirmation(ProcessingCallbackDto $dto): bool
    {
        if ($dto->confirmations < $this->minTransactionConfirmations) {
            return true;
        }
        return false;
    }

    private function createHotWallet(Store $store, string $address, Blockchain $blockchain, PayerAddress $payerAddress): void
    {
        $dto = new HotWalletDto([
            'currencyId' => CurrencyId::tryFrom($payerAddress->currency_id),
            'user'       => $store->user,
            'address'    => $address,
            'blockchain' => $blockchain
        ]);

        $this->hotWalletService->storeHotWallet($dto);
    }

    /**
     * @throws Throwable
     */
    private function createTransaction(
        ProcessingCallbackDto $dto,
        Store                 $store,
        PayerAddress          $payerAddress,
        Invoice               $invoice,
        string                $amount,
        string                $rate
    ): Transaction
    {

        $transaction = new Transaction([
            'store_id'           => $store->id,
            'user_id'            => $store->user_id,
            'invoice_id'         => $invoice->id,
            'currency_id'        => $payerAddress->currency_id,
            'tx_id'              => $dto->tx,
            'type'               => TransactionType::Invoice,
            'from_address'       => $dto->sender ?? null,
            'to_address'         => $dto->address,
            'amount'             => $dto->amount,
            'amount_usd'         => $amount,
            'rate'               => $rate,
            'fee'                => 0,
            'payer_id'           => $payerAddress->payer_id,
            'network_created_at' => $dto->time ? date('Y-m-d H:i:s', strtotime($dto->time)) : null,
        ]);
        $transaction->saveOrFail();
        return $transaction;
    }
}
