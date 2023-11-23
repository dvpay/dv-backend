<?php

namespace App\Models;

use App\Enums\Blockchain;
use App\Enums\WithdrawalInterval;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Wallet
 */
class Wallet extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'address',
        'blockchain',
        'chain',
        'store_id',
        'readonly',
        'seed',
        'pass_phrase',
        'user_id',
        'enable_automatic_exchange',
        'withdrawal_min_balance',
        'withdrawal_interval',
        'withdrawal_enabled',
        'withdrawal_interval_cron',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'blockchain'         => Blockchain::class,
        'withdrawal_enabled' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function balances(): HasMany
    {
        return $this->hasMany(WalletBalance::class, 'wallet_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasOne
     */
    public function exchange(): HasOne
    {
        return $this->hasOne(Exchange::class, 'id', 'exchange_id');
    }

    /**
     * @return HasOne
     */
    public function exchangeColdWallet(): HasOne
    {
        return $this->hasOne(ExchangeColdWallet::class, 'wallet_id', 'id');
    }

    public function exchangeKeys(): BelongsTo
    {
        return $this->belongsTo(ExchangeUserKey::class, 'user_id', 'user_id');
    }

    public function exchangeWalletCurrency(): HasOne
    {
        return $this->hasOne(ExchangeWalletCurrency::class, 'wallet_id', 'id');
    }

    public function exchangeColdWallets(): HasMany
    {
        return $this->hasMany(ExchangeColdWallet::class, 'wallet_id', 'id');
    }

    public function scopeRandomExchangeColdWallet(Builder $query): Builder
    {
        return $query->with('exchangeColdWallets')
            ->inRandomOrder()
            ->limit(1);
    }

    public function exchangeUserPairs(): HasManyThrough
    {
        return $this->hasManyThrough(ExchangeUserPairs::class, ExchangeWalletCurrency::class,'wallet_id', 'currency_from', 'id', 'from_currency_id');
    }

}
