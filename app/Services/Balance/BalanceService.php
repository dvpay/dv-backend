<?php

declare(strict_types=1);

namespace App\Services\Balance;

use App\Enums\Blockchain;
use App\Enums\CurrencyId;
use App\Enums\CurrencySymbol;
use App\Enums\InvoiceAddressState;
use App\Enums\RateSource;
use App\Models\Currency;
use App\Models\InvoiceAddress;
use App\Models\User;
use App\Models\UserInvoiceAddress;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\BalanceGetter;
use Illuminate\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;
use Exception;

/**
 * BalanceService
 */
class BalanceService
{
    /**
     * @param BalanceGetter $balanceGetter
     * @param Repository $cache
     */
    public function __construct(
        private readonly BalanceGetter $balanceGetter,
        private readonly Repository $cache
    )
    {
    }

    /**
     * @param User $user
     * @param array|null $stores
     * @return array
     */
    public function getAllBalances(User $user, ?array $stores): array
    {
        $balancesQuery = InvoiceAddress::selectRaw(
            'invoice_addresses.currency_id as currency, SUM(invoice_addresses.balance) as balance'
        )
            ->join('invoices', 'invoices.id', 'invoice_addresses.invoice_id')
            ->join('stores', 'stores.id', 'invoices.store_id')
            ->where('stores.user_id', $user->id);

        if ($stores) {
            $balancesQuery->whereIn('stores.id', $stores);
        }

        $balances = $balancesQuery->groupBy('invoice_addresses.currency_id')
            ->get()
            ->toArray();

        if (!$balances) {
            $balances = $this->getDefaultBalances();
        }

        return $balances;
    }

    /**
     * @return array
     */
    private function getDefaultBalances(): array
    {
        $balances = [];

        $addressCount = ['total' => 0];
        foreach (InvoiceAddressState::cases() as $state) {
            $addressCount[$state->value] = 0;
        }

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
                    'addressCount' => $addressCount,
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
        $addresses = $this->getAddressesGroupedByCurrencyAndState($user);

        $result = [];
        foreach ($balances as $key => $value) {
            $currency = $this->getCurrencyName($key);

            $addressCount = ['total' => 0];
            if (isset($addresses[$currency])) {
                foreach (InvoiceAddressState::cases() as $state) {
                    if (!isset($addressCount[$state->value])) {
                        $addressCount[$state->value] = 0;
                    }
                    if (isset($addresses[$currency][$state->value])) {
                        $addressCount[$state->value] = $addresses[$currency][$state->value];
                        $addressCount['total'] += $addressCount[$state->value];
                    }
                }
            }

            $result[] = [
                'currency'   => $currency,
                'balance'    => (string)$value,
                'balanceUsd' => $this->inUsd($currency, (string)$value),
                'addressCount' => $addressCount,
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

    private function getAddressesGroupedByCurrencyAndState(User $user): array
    {
        $userInvoiceAddresses = UserInvoiceAddress::selectRaw('state, currency_id, COUNT(id) AS count')
            ->where('processing_owner_id', '=', $user->processing_owner_id)
            ->groupBy('state', 'currency_id')
            ->get();

        $addresses = [];
        foreach ($userInvoiceAddresses as $address) {
            if (!isset($addresses[$address->currency_id])) {
                $addresses[$address->currency_id] = [];
            }
            $addresses[$address->currency_id][$address->state] = $address->count;
        }

        return $addresses;
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
