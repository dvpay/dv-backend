<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Enums\Blockchain;
use App\Enums\InvoiceStatus;
use App\Enums\RateSource;
use App\Exceptions\RateNotFoundException;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use App\Models\PayerAddress;
use App\Services\Currency\CurrencyRateService;
use Exception;
use Symfony\Component\HttpFoundation\Response;

readonly class InvoiceAddressCreator
{
    public function __construct(
            private CurrencyRateService $currencyService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function createAddress(Invoice $invoice, Currency $currency): string
    {
        if ($invoice->status != InvoiceStatus::Waiting) {
            throw new Exception(__('Incorrect invoice status'), Response::HTTP_CONFLICT);
        }

        $invoiceAddress = InvoiceAddress::where([
                ['invoice_id', $invoice->id],
                ['blockchain', $currency->blockchain],
                ['currency_id', $currency->id],
        ])->first();
        if ($invoiceAddress) {
            return $invoiceAddress->address;
        }

        $invoiceAddress = new InvoiceAddress([
                'invoice_id'          => $invoice->id,
                'address'             => '',
                'blockchain'          => $currency->blockchain,
                'currency_id'         => $currency->id,
                'balance'             => 0,
                'rate'                => $this->getCurrencyRate($invoice, $currency),
                'invoice_currency_id' => $invoice->currency_id,
                'exchange_rate_at'    => now()
        ]);
        $invoiceAddress->save();

        return $invoiceAddress->address;
    }

    private function getCurrencyRate(Invoice $invoice, Currency $currency): ?string
    {
        $rateSource = RateSource::fromStore($invoice->store);

        $data = $this->currencyService->getCurrencyRate(
                $rateSource,
                $currency->code,
                $invoice->currency->code
        );

        if (!$data) {
            throw new RateNotFoundException();
        }

        if ($currency->blockchain == Blockchain::Bitcoin) {
            $scale = bcmul($data['rate'], bcdiv($invoice->store->rate_scale, '100'));
            $data['rate'] = bcadd($data['rate'], $scale);
        }

        return $data['rate'];
    }

    /**
     * @throws Exception
     */


    public function updateRateInvoiceAddress(Invoice $invoice): void
    {
        foreach ($invoice->addresses as $invoiceAddress) {
            if ($invoiceAddress->exchangeRateConversion()) {
                $invoiceAddress->update([
                        'rate'             => $this->getCurrencyRate($invoice, $invoiceAddress->currency),
                        'exchange_rate_at' => now()
                ]);
            }
        }
    }

    /**
     * @param  InvoiceAddress  $invoiceAddress
     * @param  PayerAddress  $payerAddress
     * @return void
     * @throws Exception
     */
    public function updateInvoiceStaticAddress(Invoice $invoice, PayerAddress $payerAddress): void
    {
        $invoiceAddress = InvoiceAddress::where([
                ['invoice_id', $invoice->id],
                ['blockchain', $payerAddress->blockchain],
                ['currency_id', $payerAddress->currency_id],
        ])->first();

        $invoiceAddress->address = $payerAddress->address;

        if (!$invoiceAddress->save()) {
            throw new Exception(__('Invoice address not updated.'));
        }
    }
}
