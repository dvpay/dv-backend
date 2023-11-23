<?php

namespace App\Jobs;

use App\Models\InvoiceAddress;
use App\Services\Processing\Contracts\AddressContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Connection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * InvoiceAddressBalanceActualization
 */
class InvoiceAddressBalanceActualization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param string $ownerId
     */
    public function __construct(private readonly string $ownerId)
    {
    }

    /**
     * @param AddressContract $addressContract
     * @param Connection $db
     * @return void
     * @throws Throwable
     */
    public function handle(AddressContract $addressContract, Connection $db): void
    {
        return;
        try {
            $db->beginTransaction();

            $addresses = $addressContract->getAll($this->ownerId);

            foreach ($addresses as $address) {
                $invoiceAddress = InvoiceAddress::where([
                    ['address', $address['address']],
                    ['blockchain', $address['blockchain']],
                ])->first();
                if (!$invoiceAddress) {
                    continue;
                }

                $invoiceAddress->balance = (float)$address['balance'];
                $invoiceAddress->saveOrFail();
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();

            throw $e;
        }
    }
}
