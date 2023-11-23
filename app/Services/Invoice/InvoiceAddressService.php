<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Dto\InvoiceAddressesListDto;
use App\Dto\InvoiceListByAddressDto;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceAddressService
{
    public function invoiceAddressesList(InvoiceAddressesListDto $dto): ?LengthAwarePaginator
    {
        $addresses = InvoiceAddress::select(
            'stores.id as storeId',
            'stores.name as storeName',
            'invoice_addresses.invoice_id as invoiceId',
            'invoice_addresses.address as address',
            'invoice_addresses.currency_id as currencyId',
            'invoice_addresses.balance as balance',
        )
            ->join('invoices', 'invoices.id', 'invoice_addresses.invoice_id')
            ->join('stores', 'stores.id', 'invoices.store_id')
            ->where('invoice_addresses.address', '!=', '')
            ->where('stores.user_id', $dto->user->id);

        if (isset($dto->stores)) {
            $addresses->whereIn('stores.id', $dto->stores);
        }

        return $addresses->orderBy('invoice_addresses.' . $dto->sortField, $dto->sortDirection)
            ->paginate($dto->perPage);
    }

    public function getInvoicesByAddress(InvoiceListByAddressDto $dto): ?LengthAwarePaginator
    {
        $invoices = Invoice::select(
            'invoices.id',
            'invoices.order_id',
            'invoices.created_at',
            'invoices.status',
            'invoices.amount',
            'invoices.currency_id',
            'stores.name'
        )
            ->join('invoice_addresses', 'invoices.id', 'invoice_addresses.invoice_id')
            ->join('stores', 'invoices.store_id', 'stores.id')
            ->where('invoice_addresses.address', $dto->address);

        if (isset($dto->stores)) {
            $invoices->whereIn('stores.id', $dto->stores);
        }

        return $invoices->orderBy('invoices.' . $dto->sortField, $dto->sortDirection)
            ->paginate($dto->perPage);
    }
}
