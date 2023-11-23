<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Http\Resources\BaseResource;
use App\Models\InvoiceAddress;
use App\Models\Store;
use App\Models\WebhookSendHistory;
use App\Services\Currency\CurrencyConversion;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DetailInvoiceResource extends BaseResource
{
    public function __construct(
        $resource,
        private readonly Store $store,
        private readonly CurrencyConversion $currencyConversion,
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
        return [
            'id'             => $this->id,
            'slug'           => $this->slug,
            'storeId'        => $this->store_id,
            'storeName'      => $this->store->name,
            'status'         => $this->status->value,
            'createdAt'      => $this->created_at,
            'expiredAt'      => $this->expired_at,
            'description'    => $this->description,
            'custom'         => $this->custom,
            'returnUrl'      => $this->return_url,
            'successUrl'     => $this->success_url,
            'amount'         => $this->amount,
            'paidAmount'     => $this->calculatePaidAmount($this),
            'currency'       => $this->currency->code,
            'isConfirm'      => $this->is_confirm,
            'payerEmail'     => $this->payer_email,
            'payerLanguage'  => $this->payer_language,
            'addresses'      => $this->getInvoiceAddresses($this),
            'transactions'   => $this->getInvoiceTransactions($this),
            'webhookHistory' => $this->getWebhookHistory($this),
            'history'        => InvoiceHistoryCollection::make($this->history),
            'ip'             => $this->ip,
            'userAgent'      => $this->user_agent,
        ];
    }

    private function calculatePaidAmount(DetailInvoiceResource $invoice): float
    {
        $result = 0;
        foreach ($invoice->addresses as $invoiceAddress) {

            $amount = $this->currencyConversion->convert(
                amount: (string)$invoiceAddress->balance,
                rate: (string)$invoiceAddress->rate
            );

            $result = bcadd((string)$result, $amount);
        }

        return (float)number_format((float)$result, 8);
    }

    private function getInvoiceAddresses(DetailInvoiceResource $invoice): array
    {
        $result = [];

        foreach ($invoice->addresses as $invoiceAddress) {
            $result[] = [
                'address'    => $this->getAddress($invoiceAddress),
                'currency'   => $invoiceAddress->currency_id,
                'blockchain' => $invoiceAddress->blockchain,
                'balance'    => $invoiceAddress->balance,
                'rate'       => bcdiv('1', $invoiceAddress->rate),
            ];
        }

        return $result;
    }

    private function getAddress(InvoiceAddress $invoiceAddress)
    {
        if (in_array($invoiceAddress->blockchain->value, $this->disabledBlockchains)) {
            return '';
        }

        return $invoiceAddress->address;
    }

    private function getInvoiceTransactions(DetailInvoiceResource $invoice): array
    {
        $result = [];

        foreach ($invoice->transactions as $transaction) {
            $result[] = [
                'currency'   => $transaction->currency_id,
                'blockchain' => $transaction->currency->blockchain,
                'tx'         => $transaction->tx_id,
                'sender'     => $transaction->from_address,
                'createdAt'  => $transaction->created_at,
                'amount'     => $transaction->amount,
            ];
        }

        return $result;
    }

    private function getWebhookHistory(DetailInvoiceResource $invoice): Collection
    {
        $history = WebhookSendHistory::select(
            'type',
            'url',
            'status',
            'request',
            'response',
            'response_status_code as responseStatusCode',
            'created_at as createdAt'
        )
            ->where('invoice_id', $invoice->id)
            ->get();

        return $history;
    }
}
