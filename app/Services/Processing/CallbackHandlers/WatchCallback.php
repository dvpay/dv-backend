<?php

declare(strict_types=1);

namespace App\Services\Processing\CallbackHandlers;

use App\Dto\ProcessingCallbackDto;
use App\Enums\InvoiceStatus;
use App\Enums\TransactionType;
use App\Jobs\InvoiceAddressBalanceActualization;
use App\Jobs\WatchCallbackJob;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Models\Wallet;
use App\Services\Currency\CurrencyConversion;
use App\Services\Processing\Contracts\CallbackHandlerContract;
use Exception;
use Illuminate\Database\Connection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * WatchCallback
 */
class WatchCallback implements CallbackHandlerContract
{
    public function handle(ProcessingCallbackDto $dto): void
    {
        $invoiceAddress = InvoiceAddress::where([
            ['watch_id', $dto->id],
            ['blockchain', $dto->blockchain],
            ['address', $dto->address],
            ['invoice_id', $dto->invoice_id]
        ])->firstOrFail();

        WatchCallbackJob::dispatch($dto, $invoiceAddress);

    }
}
