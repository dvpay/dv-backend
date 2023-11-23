<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payer;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class GetTransactionInfoDto extends ArrayDto
{
    public readonly Transaction|ProcessingTransactionInfoDto $transaction;
    public readonly Currency $currency;
    public readonly Invoice $invoice;
    public readonly ?Collection $webhooks;
    public ?Collection $probablyRelatedInvoices = null;
    public ?Payer $payer = null;
}