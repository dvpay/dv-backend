<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Traits\HasUuid;

class InvoiceStatusHistory extends Model
{
    use HasUuid;

    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'status',
        'previous_status',
        'created_at',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'previous_status' => InvoiceStatus::class,
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}