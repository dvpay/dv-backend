<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\TransferStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'currency_id',
        'status',
        'address_from',
        'address_to',
        'amount',
        'amount_usd'
    ];

    protected $casts = [
        'status' => TransferStatus::class
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
