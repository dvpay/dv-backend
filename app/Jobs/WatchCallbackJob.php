<?php

namespace App\Jobs;

use App\Dto\ProcessingCallbackDto;
use App\Enums\InvoiceStatus;
use App\Enums\TransactionType;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Services\Currency\CurrencyConversion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class WatchCallbackJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly ProcessingCallbackDto $dto,
        private readonly InvoiceAddress        $invoiceAddress,
    )
    {
    }

    /**
     * @param ProcessingCallbackDto $dto
     * @param InvoiceAddress $invoiceAddress
     * @return void
     * @throws Throwable
     */
    public function handle(string $minTransactionConfirmations, CurrencyConversion $currencyConversion): void
    {
        Log::channel('processingLog')->info('[dto]', (array)$this->dto);

        try {
            DB::beginTransaction();

            $invoice = $this->invoiceAddress->invoice;
            $store = $invoice->store;

            if ($this->checkConfirmation($minTransactionConfirmations)) {
                $this->createUnconfirmedTransaction($this->dto, $store, $invoice, $this->invoiceAddress);
                $invoice->updateStatus(InvoiceStatus::WaitingConfirmations);
                DB::commit();
                return;
            }
            switch ($this->dto->status) {
                case InvoiceStatus::Paid:
                case InvoiceStatus::PartiallyPaid:
                    $this->updateInvoiceBalance($this->dto, $this->invoiceAddress);
                    if($this->createTransaction($this->dto, $store, $invoice, $this->invoiceAddress)) {
                        $this->updateInvoice($this->dto, $invoice, $currencyConversion);
                    }
                    break;
                case InvoiceStatus::Expired:
                    $this->updateInvoice($this->dto, $invoice, $currencyConversion);
                    break;
            }
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }

        if ($this->dto->status === InvoiceStatus::Paid || $this->dto->status === InvoiceStatus::PartiallyPaid) {
            //InvoiceAddressBalanceActualization::dispatch($store->user->processing_owner_id);
        }
    }

    private function checkConfirmation(string $minTransactionConfirmations): bool
    {
        if ($this->dto->status == InvoiceStatus::Expired || $this->dto->confirmations < $minTransactionConfirmations) {
            return true;
        }
        return false;
    }

    private function createUnconfirmedTransaction(ProcessingCallbackDto $dto, Store $store, Invoice $invoice, InvoiceAddress $invoiceAddress): void
    {
        UnconfirmedTransaction::firstOrCreate([
            'currency_id' => $invoiceAddress->currency_id,
            'tx_id'       => $dto->tx,
        ], [
            'user_id'      => $store->user_id,
            'store_id'     => $store->id,
            'invoice_id'   => $invoice->id,
            'from_address' => $dto->sender ?? '',
            'to_address'   => $dto->address,
            'tx_id'        => $dto->tx,
            'currency_id'  => $invoiceAddress->currency_id
        ]);
    }

    private function updateInvoiceBalance(ProcessingCallbackDto $dto, InvoiceAddress $invoiceAddress): void
    {
        $invoiceAddress->increment('balance', (float)$dto->amount);
    }

    /**
     * @throws Throwable
     */
    private function createTransaction(ProcessingCallbackDto $dto, Store $store, Invoice $invoice, InvoiceAddress $invoiceAddress): bool
    {
        $transactionExists = Transaction::where('currency_id', $invoiceAddress->currency_id)
            ->where('tx_id', $dto->tx)
            ->exists();

        if ($transactionExists) {
            return false;
        }

        $transaction = new Transaction([
            'store_id'           => $store->id,
            'user_id'            => $store->user_id,
            'invoice_id'         => $invoice->id,
            'currency_id'        => $invoiceAddress->currency_id,
            'tx_id'              => $dto->tx,
            'type'               => TransactionType::Invoice,
            'from_address'       => $dto->sender ?? '',
            'to_address'         => $dto->address,
            'amount'             => $dto->amount,
            'amount_usd'         => $dto->amount / $invoiceAddress->rate,
            'rate'               => $invoiceAddress->rate,
            'fee'                => 0,
            'network_created_at' => $dto->time ?? null,
        ]);
        return $transaction->saveOrFail();
    }

    /**
     * @throws \Exception
     */
    private function updateInvoice(ProcessingCallbackDto $dto, Invoice $invoice, CurrencyConversion $currencyConversion): void
    {
        if ($invoice->status == InvoiceStatus::Success) {
            return;
        }

        $sum = $invoice->addresses->filter(fn($address) => $address->balance != 0)
            ->map(function ($address) use ($currencyConversion) {
                return $currencyConversion->convert(
                    amount: (string)$address->balance,
                    rate: (string)$address->rate,
                    reverseRate: true
                );
            })
            ->reduce(fn($carry, $amount) => bcadd($carry, $amount), 0);

        $newStatus = $dto->status;

        if($sum === $invoice->amount) {
            $newStatus = InvoiceStatus::Paid;
        } elseif ($sum > $invoice->amount) {
            $newStatus = InvoiceStatus::OverPaid;
        }

        $invoice->updateStatus($newStatus);
    }
}
