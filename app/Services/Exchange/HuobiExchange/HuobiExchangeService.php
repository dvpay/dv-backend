<?php

namespace App\Services\Exchange\HuobiExchange;

use App\Dto\Exchange\DepositAddressDto;
use App\Enums\CurrencySymbol;
use App\Enums\ExchangeAddressType;
use App\Enums\ExchangeChainType;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Models\Currency;
use App\Models\ExchangeColdWalletWithdrawal;
use App\Models\ExchangeUserPairs;
use App\Models\ExchangeWithdrawalWallet;
use App\Models\User;
use App\Services\Exchange\AbstractExchange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class HuobiExchangeService extends AbstractExchange implements ExchangeServiceInterface
{
    private ExchangeServiceEnum $serviceName = ExchangeServiceEnum::Huobi;

    public function __construct(
        protected HuobiExchangeClient $huobiClient,
        protected User                $user,
    )
    {
        $this->setKeys();
    }

    public function getExchangeName(): string
    {
        return 'Huobi';
    }

    public function loadDepositAddress(): array
    {
        return $this->loadAddresses(ExchangeAddressType::Deposit);
    }

    public function loadWithdrawalAddress(): array
    {
        return $this->loadAddresses(ExchangeAddressType::Withdraw);
    }

    private function loadAddresses(ExchangeAddressType $addressType): array
    {
        $currencies = Currency::where('has_balance', true)->get();
        $addresses = [];

        foreach ($currencies as $currency) {
            $exchangeAddress = $this->getAddress($addressType, $currency->name->value);
            $exchangeAddress = $this->filterAddress($exchangeAddress);
            if (empty($exchangeAddress)) continue;
            foreach ($exchangeAddress as $address) {
                $dto = new DepositAddressDto([
                    'currency'       => $address->currency,
                    'exchangeUserId' => $address->userId ?? null,
                    'address'        => $address->address,
                    'chain'          => $address->chain,
                    'type'           => $addressType,
                ]);
                $this->saveExchangeAddress($dto, $this->huobiClient->getAccessKey(), $addressType);
            }

            $addresses = array_merge($addresses, $exchangeAddress);
        }

        return $addresses;
    }

    public function loadSymbolsByCurrency(): Collection
    {
        return Cache::remember('symbols_by_currency', 3600, function () {
            $currencies = Currency::where('has_balance', true)
                ->get()
                ->pluck('code')
                ->map(fn($item) => strtolower($item->value));

            $symbols = collect($this->huobiClient->getSymbols());

            return $symbols->filter(fn($item) => $currencies->contains($item->{'base-currency'}))
                ->map(fn($item) => [
                    'fromCurrencyId' => $item->{'base-currency'},
                    'toCurrencyId'   => $item->{'quote-currency'},
                    'symbol'         => $item->symbol
                ])
                ->groupBy('fromCurrencyId');
        });
    }

    public function loadExchangeSymbols(): Collection
    {
        return Cache::remember('exchange_by_currency', 3600, function (): Collection {
            $list = collect($this->huobiClient->getSymbolsV2());
            $sellSymbols = $list->map(fn($item) => (object)[
                'currencyFrom' => $item->bc,
                'currencyTo'   => $item->qc,
                'label'        => $item->bc . '/' . $item->qc,
                'symbol'       => $item->symbol,
                'type'         => 'sell'
            ]);

            $buySymbols = $list->map(fn($item) => (object)[
                'currencyFrom' => $item->qc,
                'currencyTo'   => $item->bc,
                'label'        => $item->qc . '/' . $item->bc,
                'symbol'       => $item->symbol,
                'type'         => 'buy'
            ]);

            return $sellSymbols->merge($buySymbols);
        });
    }

    /**
     * @throws \Throwable
     */
    public function exchange(ExchangeUserPairs $exchangeUserPairs): ?object
    {
        $accounts = collect($this->huobiClient->getAccounts());

        $spotAccount = $accounts->where('state', '=', 'working')
            ->firstWhere('type', '=', 'spot');

        if (!$spotAccount) {
            Log::channel('exchangeLog')->error('No working spot account found');
            return null;
        }

        $balances = collect($this->huobiClient->getBalance(data: [], accountID: $spotAccount->id)->list);

        $balanceTicker = $balances->where('currency', $exchangeUserPairs->currency_from)
            ->firstWhere('type', '=', 'trade');

        if (!$balanceTicker) {
            Log::channel('exchangeLog')->error('Balance ticker not found');
            return null;
        }


        Log::channel('exchangeLog')->error('ExchangeWithdrawal (balances)', [$balanceTicker]);

        $amount = floor((float)$balanceTicker->balance * 10000) / 10000;

        $result = $this->huobiClient->placeOrder([
            'account-id' => $spotAccount->id,
            'symbol'     => $exchangeUserPairs->symbol,
            'type'       => $exchangeUserPairs->type . '-market',
            'amount'     => $amount,
            'source'     => 'api',
        ]);

        Log::channel('exchangeLog')->error('ExchangeWithdrawal (placeOrder)', [$result]);

        if ($result->status != 'ok') {
            Log::channel('exchangeLog')->error('ExchangeWithdrawal (error)', [$result]);
            return null;
        }

        return $result;
    }

    /**
     * @throws \Throwable
     */
    public function withdrawalFromExchange(): void
    {
        $accounts = collect($this->huobiClient->getAccounts());

        $spotAccount = $accounts->where('state', '=', 'working')
            ->firstWhere('type', '=', 'spot');

        if (!$spotAccount) {
            Log::channel('exchangeLog')->error('No working spot account found for user:' . $this->user->id);
            return;
        }

        $wallets = ExchangeWithdrawalWallet::select()
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY currency ORDER BY RAND()) AS rn')
            ->where('user_id', $this->user->id)
            ->where('exchange_id', $this->serviceName->getId())
            ->where('is_withdrawal_enable', true)
            ->get()
            ->where('rn', 1);

        $balances = collect($this->huobiClient->getBalance(data: [], accountID: $spotAccount->id)->list);

        foreach ($wallets as $wallet) {
            $balanceTicker = $balances->where('balance', '>', 0)
                ->where('type', '=', 'trade')
                ->where('currency', '=', $wallet->currency)
                ->first();

            if ($balanceTicker->balance < $wallet->min_balance) {
                continue;
            };


            $withdrawalAmount = (float)$balanceTicker->balance;
            $withdrawalAmountCorrection = config('exchange.withdrawalAmountCorrectionUsdt');

            if ($wallet->currency !== CurrencySymbol::USDT->toLower()) {
                $withdrawalAmountCorrection = $this->calculateFromUsdt($wallet->currency, $withdrawalAmountCorrection);
            }

            $withdrawalAmount = floor(($withdrawalAmount - $withdrawalAmountCorrection) * 10000) / 10000;

            if ($withdrawalAmount < 0) {
                continue;
            }

            $result = $this->huobiClient->placeWithdrawal([
                'address'  => $wallet->address,
                'amount'   => $withdrawalAmount,
                'currency' => $wallet->currency,
                'chain'    => $wallet->chain,
            ]);

            if (empty($result->status) || $result->status != 'ok') {
                Log::channel('exchangeLog')->error('Withdrawal failed', [$result]);
                continue;
            }

            Log::channel('exchangeLog')->info('Withdrawal completed', [$result]);

            $exchangeColdWalletWithdrawal = new ExchangeColdWalletWithdrawal([
                'exchange_cold_wallet_id' => $wallet->id,
                'exchange_id'             => ExchangeServiceEnum::Huobi->getId(),
                'address'                 => (string)$wallet->address,
                'amount'                  => (string)$withdrawalAmount,
            ]);
            $exchangeColdWalletWithdrawal->saveOrFail();
        };

    }

    public function calculateUsdt(string $symbol, float $amount)
    {
        $detail = $this->huobiClient->getMarketDetail(['symbol' => $symbol]);
        return $amount * $detail->tick->open;
    }

    public function calculateFromUsdt(string $symbol, float $amount)
    {
        $detail = $this->huobiClient->getMarketDetail(['symbol' => $symbol]);
        return $amount / $detail->tick->open;
    }

    public function setKeys(): void
    {
        $keys = $this->getKeys($this->serviceName);
        $this->huobiClient->setAccessKey($keys['accessKey']);
        $this->huobiClient->setSecretKey($keys['secretKey']);
    }

    /**
     * @throws \Exception
     */
    public function getExchangeBalance(): Collection
    {
        $accounts = collect($this->huobiClient->getAccounts());

        $spotAccount = $accounts->where('state', '=', 'working')
            ->firstWhere('type', '=', 'spot');

        return collect($this->huobiClient->getBalance([], $spotAccount->id)->list);
    }

    private function filterAddress($addresses): array
    {
        return array_filter($addresses, fn($item) => in_array($item->chain, ExchangeChainType::values()));
    }

    private function getAddress(ExchangeAddressType $addressType, string $currencyName): array
    {

        if ($addressType === ExchangeAddressType::Deposit) {
            return $this->huobiClient->getDepositAddress(['currency' => strtolower($currencyName)]);
        }

        return $this->huobiClient->getWithdrawalAddress(['currency' => strtolower($currencyName)]);
    }

    public function testConnection(): bool
    {
        $account = $this->huobiClient->getAccounts();
        if ($account) {
            return true;
        }
        return false;
    }

}
