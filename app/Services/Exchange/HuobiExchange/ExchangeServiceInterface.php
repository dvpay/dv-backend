<?php

namespace App\Services\Exchange\HuobiExchange;

use App\Dto\Exchange\UserPairsDto;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Models\ExchangeUserPairs;
use Illuminate\Support\Collection;

interface ExchangeServiceInterface
{
    public function getExchangeName(): string;
    public function loadDepositAddress(): array;

    public function loadWithdrawalAddress(): array;

    /*
     * Method for withdrawal pseudo random address
     * on cold wallets
     * */
    public function withdrawalFromExchange(): void;

    public function getKeys(ExchangeServiceEnum $exchangeServiceEnum): array;

    public function loadExchangeSymbols(): Collection;

    public function getExchangeBalance(): Collection;

    public function testConnection(): bool;

    public function saveExchangeUserPairs(UserPairsDto $dto): void;

    public function exchange(ExchangeUserPairs $exchangeUserPairs): ?object;

    public function loadSymbolsByCurrency(): Collection;

}