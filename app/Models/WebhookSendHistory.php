<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebhookStatus;
use App\Enums\WebhookType;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WebhookSendHistory extends Model
{
    use HasUuid;

    protected $primaryKey = 'id';

    protected $fillable = [
        'invoice_id',
        'type',
        'url',
        'status',
        'request',
        'response',
        'response_status_code',
        'tx_hash',
    ];

    protected $casts = [
        'type' => WebhookType::class,
        'status' => WebhookStatus::class,
        'request' => 'json',
        'response' => 'json',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}