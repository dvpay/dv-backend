<?php

namespace App\Models;

use App\Enums\Blockchain;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalWallet extends Model
{
    use SoftDeletes, HasUuid, HasFactory;

    protected $fillable = [
        'user_id',
        'exchange_id',
        'chain',
        'blockchain',
        'currency',
        'type',
        'withdrawal_enabled',
        'withdrawal_min_balance',
        'withdrawal_interval',
    ];

    protected $casts = [
        'withdrawal_enabled' => 'boolean',
        'blockchain' => Blockchain::class
    ];

    public function address(): HasMany
    {
        return $this->hasMany(WithdrawalWalletAddress::class, 'withdrawal_wallet_id', 'id');
    }

    public function exchange(): HasOne
    {
        return $this->hasOne(Exchange::class, 'id', 'exchange_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function hotWallets(): HasMany
    {
        return $this->hasMany(HotWallet::class,'user_id', 'user_id');
    }
}
