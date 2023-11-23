<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Dto\InvoiceAddressesListDto;
use App\Enums\InvoiceAddressState;
use App\Models\UserInvoiceAddress;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * InvoiceAddressService
 */
class InvoiceAddressServiceNew
{
    /**
     * @param InvoiceAddressesListDto $dto
     * @return mixed
     */
    public function invoiceAddressesList(InvoiceAddressesListDto $dto): LengthAwarePaginator
    {
        $result = UserInvoiceAddress::where('processing_owner_id', $dto->user->processing_owner_id)
            ->orderBy($dto->sortField, $dto->sortDirection);

        if (isset($dto->filterField)) {
            $result->where($dto->filterField, $dto->filterValue);
        }

	    if ($dto->hideEmpty) {
		    $result->where('balance_usd', '>=', 1)
		           ->orWhere([
                       ['processing_owner_id', $dto->user->processing_owner_id],
                       ['balance_usd', '<', 1],
			           ['state', InvoiceAddressState::Busy->value],
		           ]);
	    }

        return $result->paginate($dto->perPage);
    }
}