<?php

declare(strict_types=1);

namespace App\Services\Exchange;

use App\Dto\ExchangeKeyAddDto;
use App\Enums\ExchangeChainType;
use App\Enums\ExchangeKeyType;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Exceptions\ApiException;
use App\Models\Exchange;
use App\Models\ExchangeKey;
use App\Models\ExchangeUserKey;
use App\Models\ExchangeWalletCurrency;
use App\Models\User;
use App\Services\Huobi\HuobiService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * @param $userId
     *
     * @return array
     */
    public function getHuobiKeys($userId): array
    {
        $userKeys = ExchangeUserKey::select('exchange_keys.key as name', 'exchange_user_keys.value as value')
            ->join('exchange_keys', 'exchange_user_keys.key_id', 'exchange_keys.id')
            ->where([
                ['exchange_keys.exchange_id', ExchangeServiceEnum::Huobi->getId()],
                ['exchange_user_keys.user_id', $userId],
            ])
            ->get();

        $result = [];
        foreach ($userKeys as $key) {
            if ($key->name == ExchangeKeyType::AccessKey->value) {
                $result['accessKey'] = $key->value;
            }

            if ($key->name == ExchangeKeyType::SecretKey->value) {
                $result['secretKey'] = $key->value;
            }
        }

        return $result;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     * @throws Throwable
     */
    public function testConnection(User $user, ExchangeServiceEnum $exchange): void
    {
        if ($exchange == ExchangeServiceEnum::Huobi) {
            $keys = $this->getHuobiKeys($user->id);
        }

        if (!isset($keys['accessKey']) || !isset($keys['secretKey'])) {
            throw new ApiException('Keys not found', Response::HTTP_BAD_REQUEST);
        }

        $huobiService = new HuobiService(
            $keys['accessKey'],
            $keys['secretKey'],
            $user
        );

        $result = $huobiService->getAccountAccounts();
        if ($result->status != 'ok') {
            throw new ApiException('Test connection - failed.', Response::HTTP_BAD_REQUEST);
        }
    }
    public function loadAddress(Authenticatable|User $user, ExchangeServiceEnum $exchange)
    {


        if ($exchange == ExchangeServiceEnum::Huobi) {
            $keys = $this->getHuobiKeys($user->id);
        }

        if (!isset($keys['accessKey']) || !isset($keys['secretKey'])) {
            throw new ApiException('Keys not found', Response::HTTP_BAD_REQUEST);
        }

        $huobiService = new HuobiService(
            $keys['accessKey'],
            $keys['secretKey'],
            $user
        );

        $result = $huobiService->getDepositAddresses();

        return array_filter($result, function($item) {
            return in_array($item->chain, ExchangeChainType::values());
        });

    }
}