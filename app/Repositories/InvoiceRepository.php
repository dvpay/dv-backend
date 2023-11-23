<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Invoice;

class InvoiceRepository
{
    public function getById($id): ?Invoice
    {
        return Invoice::find($id);
    }
}