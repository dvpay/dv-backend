<?php

namespace App\Services\Exchange;

use App\Dto\Exchange\DepositAddressDto;
use App\Dto\Exchange\UserPairsDto;
use App\Enums\ExchangeAddressType;
use App\Enums\ExchangeKeyType;
use App\Models\ExchangeAddress;
use App\Models\ExchangeUserKey;
use App\Models\ExchangeUserPairs;
use App\Models\User;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use Illuminate\Contracts\Auth\Authenticatable;


abstract class AbstractExchange
{

    protected $config;
    protected User $user;

    abstract public function getExchangeName();

    abstract public function loadDepositAddress();

    public function getKeys(ExchangeServiceEnum $exchangeServiceEnum): array
    {
        $userKeys = ExchangeUserKey::with(['exchangeKey.exchangeService'])
            ->whereHas('exchangeKey.exchangeService',
                fn($query) => $query->where('id', $exchangeServiceEnum->getId()))
            ->where('user_id', $this->user->id)
            ->get();
        $result = [];
        foreach ($userKeys as $key) {
            if ($key->exchangeKey->key->value === ExchangeKeyType::AccessKey->value) {
                $result['accessKey'] = $key->value;
            } elseif ($key->exchangeKey->key->value === ExchangeKeyType::SecretKey->value) {
                $result['secretKey'] = $key->value;
            }
        }
        return $result;
    }

    public function saveExchangeAddress(DepositAddressDto $dto, string $accessKey, ExchangeAddressType $addressType): void
    {
        ExchangeAddress::firstOrCreate([
            'exchange_key' => $accessKey,
            'address' => $dto->address,
            'chain' => $dto->chain,
            'currency' => $dto->currency,
            'address_type' => $addressType->value,
            'exchange_user_id' => $dto->exchangeUserId ?? null,
            'user_id' => $this->user->id
        ]);
    }


    public function saveExchangeUserPairs(UserPairsDto $dto): void
    {
        ExchangeUserPairs::create([
            'user_id' => $dto->userId,
            'exchange_id' => $dto->exchangeId,
            'currency_from' => $dto->currencyFrom,
            'currency_to' => $dto->currencyTo,
            'symbol' => $dto->symbol,
            'type' => $dto->type,
        ]);
    }

    public function deleteExchangeUserPairs(int $exchangeId): void
    {
        ExchangeUserPairs::where('user_id', $this->user->id)
            ->where('exchange_id', $exchangeId)
            ->forceDelete();
    }

    public static function make(...$params): static
    {
        return new static(...$params);
    }

    public function setConfig($config): void
    {
        $this->config = $config;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

}