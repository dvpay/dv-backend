<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Dto\GetListInvoicesDto;
use App\Enums\InvoiceStatus;
use App\Exceptions\ApiException;
use App\Models\Invoice;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvoiceService
{
    public function invoiceList(GetListInvoicesDto $dto): Paginator
    {
        $invoicesQuery = Invoice::select(
            'invoices.id',
            'invoices.order_id',
            'invoices.created_at',
            'invoices.status',
            'invoices.amount',
            'invoices.currency_id',
            'stores.name'
        )
            ->join('stores', 'stores.id', 'invoices.store_id')
            ->where('stores.user_id', $dto->user->id);

        if (isset($dto->query)) {
            $invoicesQuery = $this->queryExist($dto, $invoicesQuery);
        }

        if (isset($dto->stores)) {
            $invoicesQuery->whereIn('invoices.store_id', $dto->stores);
        }

        return $invoicesQuery
            ->orderBy($dto->sortField, $dto->sortDirection)
            ->paginate($dto->perPage);
    }

    private function queryExist(GetListInvoicesDto $dto, Builder $invoicesQuery): Builder
    {
        $invoice = Invoice::where('order_id', $dto->query)->exists();
        if ($invoice) {
            return $invoicesQuery->where('invoices.order_id', $dto->query);
        }

        return $invoicesQuery->where('invoices.id', $dto->query);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function confirm(Invoice $invoice): void
    {
        if (
            $invoice->status != InvoiceStatus::WaitingConfirmations
            && $invoice->status != InvoiceStatus::PartiallyPaid
        ) {
            throw new ApiException('Change invoice status - failed', Response::HTTP_BAD_REQUEST);
        }

        $invoice->is_confirm = true;
        $invoice->confirmed_at = date('Y-m-d H:i:s');
        $invoice->saveOrFail();
    }

    /**
     * @throws Throwable
     */
    public function saveEmail(Invoice $invoice, ?string $email, string $lang): void
    {
        $invoice->payer_email = $email;
        $invoice->payer_language = $lang;

        $invoice->saveOrFail();
    }

    public function writeUserAgent(Invoice $invoice, Request $request): void
    {
        $invoice->ip = $request->ip();
        $invoice->user_agent = $request->userAgent();

        $invoice->saveOrFail();

    }
}
