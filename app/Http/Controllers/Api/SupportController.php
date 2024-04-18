<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Payer\PayerAddressResource;
use App\Http\Resources\Transaction\TransactionInfoResource;
use App\Models\Invoice;
use App\Services\Payer\PayerAddressService;
use App\Services\Transaction\TransactionService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * SupportController
 */
class SupportController extends ApiController
{
    /**
     * @param TransactionService $transactionService
     */
    public function __construct(
        private readonly TransactionService  $transactionService,
        private readonly PayerAddressService $payerAddressService
    )
    {
    }

    /**
     * @param Request $request
     * @param string $txId
     * @return PayerAddressResource|TransactionInfoResource
     * @throws InvalidArgumentException
     */
    public function getTransactionInfo(Request $request, string $txId): PayerAddressResource|TransactionInfoResource
    {
        if (strlen($txId) < 64) {
            $result = $this->payerAddressService->getPayerByAddress($txId);
            return PayerAddressResource::make($result);
        } else {
            $result = $this->transactionService->getTransactionInfo($txId, $request->user(), $request->input('subDays'));
            return TransactionInfoResource::make($result);
        }

    }

    /**
     * @param string $txId
     * @param Invoice $invoice
     * @param Authenticatable $user
     * @return void
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function forceAttachTransactionToInvoice(string $txId, Invoice $invoice, Authenticatable $user): DefaultResponseResource
    {
        $this->transactionService->forceAttachTransactionToInvoice($txId, $invoice, $user);
        return (new DefaultResponseResource([]));
    }

    /**
     * @param string $txId
     * @param Authenticatable $user
     * @return DefaultResponseResource
     * @throws InvalidArgumentException
     */
    public function attachTransactionToPayer(string $txId, Authenticatable $user): DefaultResponseResource
    {
        $this->transactionService->attachTransactionToPayer($txId, $user);
        return (new DefaultResponseResource([]));

    }
}
