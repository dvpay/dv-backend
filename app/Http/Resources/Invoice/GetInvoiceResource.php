<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseResource;
use App\Models\Currency;
use App\Models\InvoiceAddress;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Services\Currency\CurrencyConversion;
use Exception;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;

/**
 * GetInvoiceResource
 */
class GetInvoiceResource extends BaseResource
{
    /**
     * @param $resource
     * @param CurrencyConversion $currencyConversion
     * @param Repository $cache
     * @param array $disabledBlockchains
     */
    public function __construct(
        $resource,
        private readonly CurrencyConversion $currencyConversion,
        private readonly Repository $cache,
        private readonly array $disabledBlockchains = []
    )
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        $paymentMethod = $this->cache->get($this->id);
        $leftAmountUsd = $this->getLeftAmountUsd($this);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'store' => $this->store->name,
            'storeStatus' => $this->store->status,
            'currency' => $this->currency_id,
            'amount' => (string)$this->amount,
            'leftAmount' => $leftAmountUsd,
            'returnUrl' => $this->return_url,
            'successUrl' => $this->success_url,
            'expiredAt' => $this->expired_at,
            'description' => $this->description,
            'status' => $this->status->value,
            'paymentMethod' => $paymentMethod,
            'isConfirm' => $this->is_confirm,
            'payerEmail' => $this->payer_email,
            'payerLanguage' => $this->payer_language,
            'addresses' => $this->getInvoiceAddresses($this, $leftAmountUsd),
        ];
    }

    /**
     * @param GetInvoiceResource $invoice
     * @param string $leftAmountUsd
     * @return array
     */
    private function getInvoiceAddresses(GetInvoiceResource $invoice, string $leftAmountUsd): array
    {
        $result = [];

        foreach ($invoice->addresses as $invoiceAddress) {
            $leftAmount = $this->currencyConversion->convert(
                amount: $leftAmountUsd,
                rate: (string)$invoiceAddress->rate
            );

            $result[] = [
                'address' => $this->getAddress($invoiceAddress),
                'currency' => $invoiceAddress->currency_id,
                'blockchain' => $invoiceAddress->blockchain,
                'amount' => $this->calculateInvoiceAmount($invoice, $invoiceAddress),
                'leftAmount' => $leftAmount,
                'leftAmountUsd' => (int)$leftAmountUsd,
                'rate' => bcdiv('1', $invoiceAddress->rate),
                'transactions' => $this->getTransactions($invoiceAddress),
            ];
        }

        return $result;
    }

    /**
     * @param InvoiceAddress $invoiceAddress
     * @return string
     */
    private function getAddress(InvoiceAddress $invoiceAddress): string
    {
        if (in_array($invoiceAddress->blockchain->value, $this->disabledBlockchains)) {
            return '';
        }

        return $invoiceAddress->address;
    }

    /**
     * @param GetInvoiceResource $invoice
     * @param InvoiceAddress $invoiceAddress
     * @return string
     */
    private function calculateInvoiceAmount(GetInvoiceResource $invoice, InvoiceAddress $invoiceAddress): string
    {
        $amount = $this->currencyConversion->convert(
            amount: (string)$invoice->amount,
            rate: (string)$invoiceAddress->rate
        );

        return $amount;
    }

    /**
     * @param GetInvoiceResource $invoice
     * @return string
     */
    private function getLeftAmountUsd(GetInvoiceResource $invoice): string
    {
        $usdPaidAmount = Transaction::where('invoice_id', $invoice->id)->sum('amount_usd');

        if ($usdPaidAmount < $invoice->amount) {
            $res = bcsub((string)$invoice->amount, (string)$usdPaidAmount);

            return rtrim($res, '0');
        }

        return (string)$invoice->amount;
    }

    private function getTransactions(InvoiceAddress $invoiceAddress): array
    {
        $confirmedTxId = Transaction::select('tx_id', 'currency_id')
            ->where('invoice_id', $invoiceAddress->invoice_id)
            ->where('to_address', $invoiceAddress->address)
            ->get()
            ->toArray();

        $unconfirmedTxId = UnconfirmedTransaction::select('tx_id', 'currency_id')
            ->where('invoice_id', $invoiceAddress->invoice_id)
            ->where('to_address', $invoiceAddress->address)
            ->get()
            ->toArray();

        $result = [];
        $transactions = array_merge($confirmedTxId, $unconfirmedTxId);
        foreach ($transactions as $transaction) {
            $currency = Currency::find($transaction['currency_id']);

            $result[] = [
                'txId' => $transaction['tx_id'],
                'linkToExplorer' => $currency->blockchain->getExplorerUrl() . '/' . $transaction['tx_id'],
            ];
        }

        return $result;
    }
}
