<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payer extends Model
{
    use SoftDeletes, HasUuid, HasFactory;

    protected $fillable = [
        'store_id',
        'store_user_id',
    ];

    public function payerAddresses(): HasMany
    {
        return $this->hasMany(PayerAddress::class, 'payer_id', 'id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
