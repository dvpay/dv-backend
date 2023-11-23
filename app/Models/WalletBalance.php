<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WalletBalance extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'wallet_id',
        'currency_id',
        'balance',
    ];

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'id', 'wallet_id');
    }

    protected function setKeysForSaveQuery($query): Builder
    {
        return $query->where(['wallet_id' => $this->wallet_id, 'currency_id' => $this->currency_id]);
    }
}
