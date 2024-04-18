<?php

declare(strict_types=1);

namespace App\Services\Exchange;

use App\Dto\ExchangeKeyAddDto;
use App\Models\Exchange;
use App\Models\ExchangeKey;
use App\Models\ExchangeUserKey;
use App\Models\ExchangeWalletCurrency;
use App\Models\User;
use Exception;
use Throwable;

/**
 * ExchangeService
 */
class ExchangeService
{
    /**
     * @throws Throwable
     */
    public function withdrawalExchangeSetting(string $walletId, string $fromCurrencyId, string $toCurrencyId): void
    {
        $exchangeSetting = ExchangeWalletCurrency::where([
            ['wallet_id', $walletId],
            ['from_currency_id', $fromCurrencyId],
            ['to_currency_id', $toCurrencyId],
        ])->first();

        if ($exchangeSetting) {
            return;
        }

        $exchangeSetting = new ExchangeWalletCurrency([
            'wallet_id'        => $walletId,
            'from_currency_id' => $fromCurrencyId,
            'to_currency_id'   => $toCurrencyId,
        ]);

        $exchangeSetting->saveOrFail();
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getKeys(User $user): array
    {
        $exchanges = Exchange::where('is_active', true)->get();

        $result = [];
        foreach ($exchanges as $exchange) {
            $keys['exchange'] = $exchange->name;
            $keys['keys'] = [];
            $exchangeKeys = ExchangeKey::where('exchange_id', $exchange->id)->get();
            foreach ($exchangeKeys as $exchangeKey) {
                $exchangeUserKey = ExchangeUserKey::where([
                    ['user_id', $user->id],
                    ['key_id', $exchangeKey->id],
                ])->first();

                $keys['keys'][] = [
                    'name'  => $exchangeKey->key,
                    'title' => $exchangeKey->key->title(),
                    'value' => $exchangeUserKey?->value,
                ];
            }

            $result[] = $keys;
        }

        return $result;
    }

    /**
     * @throws Throwable
     */
    public function addKey(ExchangeKeyAddDto $dto): void
    {
        $exchange = Exchange::where([
            ['name', $dto->exchange],
            ['is_active', true],
        ])->first();
        if (!$exchange) {
            throw new Exception('Incorrect exchange name.');
        }

        foreach ($dto->keys as $key) {
            $exchangeKey = ExchangeKey::where([
                ['exchange_id', $exchange->id],
                ['key', $key['name']],
            ])->first();

            if (!$exchangeKey) {
                continue;
            }

            $exchangeUserKey = ExchangeUserKey::where([
                ['user_id', $dto->user->id],
                ['key_id', $exchangeKey->id],
            ])->first();

            if ($exchangeUserKey && empty($key['value'])) {
                $exchangeUserKey->delete();
                continue;
            }

            if ($exchangeUserKey) {
                $exchangeUserKey->value = $key['value'];
            } else {
                $exchangeUserKey = new ExchangeUserKey([
                    'user_id' => $dto->user->id,
                    'key_id'  => $exchangeKey->id,
                    'value'   => $key['value'],
                ]);
            }

            $exchangeUserKey->saveOrFail();
        }
    }

}