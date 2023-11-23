<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StoreApiKey extends Model
{
    use HasUuid;

    protected $primaryKey = 'id';

    protected $fillable = [
        'store_id',
        'key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }
}