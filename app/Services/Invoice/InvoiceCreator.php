<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Dto\CreateInvoiceDto;
use App\Enums\Blockchain;
use App\Enums\InvoiceStatus;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payer;
use App\Models\Store;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Cache\Repository;
use Illuminate\Database\Connection;
use Throwable;

class InvoiceCreator
{
    public function __construct(
            private readonly InvoiceAddressCreator $invoiceAddressCreator,
            private readonly Connection            $db,
            private readonly Repository            $cache
    ) {
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function store(CreateInvoiceDto $dto, Store $store): Invoice
    {
        try {
            $this->db->beginTransaction();

            $expiredAt = $this->calculateExpiredAt($store);

            if (isset($dto->custom)) {
                $custom = json_encode($dto->custom);
            } else {
                $custom = null;
            }

            $invoice = Invoice::create([
                    'slug'        => null,//$dto->slug ?? null,
                    'status'      => InvoiceStatus::Waiting,
                    'store_id'    => $store->id,
                    'order_id'    => $dto->orderId,
                    'currency_id' => $dto->currencyId,
                    'amount'      => $dto->amount ?? 0.0,
                    'description' => $dto->description ?? null,
                    'return_url'  => $dto->returnUrl ?? null,
                    'success_url' => $dto->successUrl ?? null,
                    'expired_at'  => $expiredAt,
                    'destination' => $dto->destination ?? null,
                    'custom'      => $custom,
                    'payer_id'    => $dto->payer->id ?? null,
            ]);

            $blockchains = Blockchain::cases();
            foreach ($blockchains as $blockchain) {
                $currencies = Currency::where([
                        ['blockchain', $blockchain],
                        ['has_balance', true],
                ])->get();
                foreach ($currencies as $currency) {
                    $this->invoiceAddressCreator->createAddress($invoice, $currency);
                }
            }

            if (isset($dto->paymentMethod)) {
                $this->cache->set($invoice->id, $dto->paymentMethod, $expiredAt->format('U') - time());
            }

            $this->db->commit();

            return $invoice;
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    private function calculateExpiredAt(Store $store): DateTime
    {
        $dt = new DateTime();
        $dt->add(DateInterval::createFromDateString($store->invoice_expiration_time.' minutes'));

        return $dt;
    }

}
