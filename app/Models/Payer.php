<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payer extends Model
{
    use SoftDeletes, HasUuid;

    protected $fillable = [
            'store_id',
            'store_user_id',
    ];

    public function payerAddresses(): HasMany
    {
        return $this->hasMany(PayerAddress::class,'payer_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
}
