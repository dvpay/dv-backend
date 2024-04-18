<?php

declare(strict_types=1);

namespace App\Services\Balance;

use App\Enums\Blockchain;
use App\Enums\CurrencyId;
use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Models\Currency;
use App\Models\User;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\BalanceGetter;
use Illuminate\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;
use Exception;

/**
 * BalanceService
 */
readonly class BalanceService
{
    /**
     * @param BalanceGetter $balanceGetter
     * @param Repository $cache
     */
    public function __construct(
        private BalanceGetter $balanceGetter,
    )
    {
    }

    /**
     * @return array
     */
    private function getDefaultBalances(): array
    {
        $balances = [];

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where([
                ['blockchain', $blockchain],
                ['has_balance', true],
            ])->get();
            foreach ($currencies as $currency) {
                $balances[] = [
                    'currency' => $currency->id,
                    'balance' => '0',
                    'balanceUsd' => '0',
                ];
            }
        }

        return $balances;
    }

	/**
	 * @param User $user
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
    public function getAllBalanceFromProcessing(User $user): array
    {

        $balances = $this->balanceGetter->getBalanceByOwnerStoreId($user->processing_owner_id);
        $result = [];
        foreach ($balances as $key => $value) {
            $currency = $this->getCurrencyName($key);
            $result[] = [
                'currency'   => $currency,
                'balance'    => (string)$value,
                'balanceUsd' => $this->inUsd($currency, (string)$value),
            ];
        }

        if ($result == []) {
            $result = $this->getDefaultBalances();
        }

        return $result;
    }

    /**
     * @param string $key
     * @return string
     */
    private function getCurrencyName(string $key): string
    {
        if ($key == strtolower(CurrencySymbol::BTC->value)) {
            return CurrencySymbol::BTC->value . '.' . Blockchain::Bitcoin->name;
        } elseif ($key == Blockchain::Tron->value) {
            return CurrencySymbol::USDT->value . '.' . Blockchain::Tron->name;
        }

        return 'Unknown';
    }


	/**
	 * @param string $currencyId
	 * @param string $balance
	 *
	 * @return string
	 * @throws Exception
	 */
	private function inUsd(string $currencyId, string $balance): string
	{
		$from = match ($currencyId) {
			CurrencyId::UsdtTron->value => CurrencySymbol::USDT,
			CurrencyId::BtcBitcoin->value => CurrencySymbol::BTC,
			default => throw new Exception('Undefined blockchain for currency id ' . $currencyId),
		};

		$currencyRateService = app(CurrencyRateService::class);

		return (string)$currencyRateService->inUsd(
			RateSource::Binance,
			$from,
			CurrencySymbol::USD,
			$balance,
			true
		);
	}
}
