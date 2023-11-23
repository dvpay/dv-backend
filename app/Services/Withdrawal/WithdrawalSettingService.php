<?php

declare(strict_types=1);

namespace App\Services\Withdrawal;

use App\Dto\Exchange\ColdWalletDto;
use App\Dto\WithdrawalSettingGet;
use App\Dto\WithdrawalSettingUpdate;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Http\Requests\Exchange\WalletSettingsRequest;
use App\Models\Exchange;
use App\Models\ExchangeColdWallet;
use App\Models\ExchangeUserKey;
use App\Models\ExchangeWalletCurrency;
use App\Models\ExchangeWithdrawalWallet;
use App\Models\Wallet;
use App\Services\Exchange\ExchangeService;
use App\Services\Processing\Contracts\OwnerContract;
use Exception;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * WithdrawalSettingService
 */
class WithdrawalSettingService
{
    /**
     * @param OwnerContract $ownerContract
     * @param ExchangeService $exchangeService
     */
    public function __construct(
        private readonly OwnerContract   $ownerContract,
        private readonly ExchangeService $exchangeService
    )
    {
    }

    /**
     * @param string $walletId
     * @param WithdrawalSettingUpdate $settings
     *
     * @return void
     * @throws Throwable
     */
    public function update(string $walletId, WithdrawalSettingUpdate $settings): void
    {
        $wallet = Wallet::find($walletId);

        if (!$wallet) {
            throw new NotFoundHttpException(__('Wallet not found.'));
        }

        $this->updateWithdrawalSettings($wallet, $settings);

        $this->updateExchangeSettings($wallet, $settings);
    }

    /**
     * @throws Throwable
     */
    private function updateWithdrawalSettings(Wallet $wallet, WithdrawalSettingUpdate $settings): void
    {
        if (isset($settings->enabled)) {
            $wallet->withdrawal_enabled = $settings->enabled;
        }

        if (isset($settings->address)) {
            $this->updateWalletInProcessing($wallet, $settings->address);
            $wallet->address = $settings->address;
        }

        if (isset($settings->minBalance)) {
            $wallet->withdrawal_min_balance = $settings->minBalance;
        }

        if (isset($settings->withdrawalIntervalCron)) {
            $wallet->withdrawal_interval_cron = $settings->withdrawalIntervalCron;
        }

        $wallet->saveOrFail();
    }

    /**
     * @param Wallet $wallet
     * @param string $address
     * @return void
     */
    private function updateWalletInProcessing(Wallet $wallet, string $address): void
    {
        $user = $wallet->user;

        $this->ownerContract->attachColdWalletWithAddress($wallet->blockchain, $user->processing_owner_id, $address);
    }

    /**
     * @throws Throwable
     */
    private function updateExchangeSettings(Wallet $wallet, WithdrawalSettingUpdate $settings): void
    {
        if (
            !isset($settings->enableAutomaticExchange)
            || !isset($settings->exchange)
            || !isset($settings->exchangeCurrencies)
        ) {
            return;
        }

        $exchange = Exchange::where('slug', $settings->exchange)->first();
        if (!$exchange) {
            throw new Exception('Invalid exchange name.');
        }

        $wallet->enable_automatic_exchange = $settings->enableAutomaticExchange;
        $wallet->exchange_id = $exchange->id;

        $wallet->saveOrFail();

        ExchangeWalletCurrency::where('wallet_id', $wallet->id)->delete();
        foreach ($settings->exchangeCurrencies as $currency) {
            $this->exchangeService->withdrawalExchangeSetting(
                $wallet->id,
                $currency['fromCurrencyId'],
                $currency['toCurrencyId']
            );
        }

        // Exchange cold wallet

        if (!empty($settings->exchangeColdWalletAddress)) {

            $exchangeColdWallet = ExchangeColdWallet::where('wallet_id', $wallet->id)->first();

            if (!$exchangeColdWallet) {
                $exchangeColdWallet = new ExchangeColdWallet();
                $exchangeColdWallet->wallet_id = $wallet->id;
            }

            $exchangeColdWallet->address = $settings->exchangeColdWalletAddress;
            $exchangeColdWallet->is_withdrawal_enabled = $settings->exchangeColdWalletIsEnabled;
            $exchangeColdWallet->withdrawal_min_balance = $settings->exchangeColdWalletMinBalance;
            $exchangeColdWallet->chain = $settings->exchangeChain;

            $exchangeColdWallet->saveOrFail();
        } else {
            ExchangeColdWallet::where('wallet_id', $wallet->id)->delete();
        }
    }

    /**
     * @param string|Wallet $wallet
     *
     * @return WithdrawalSettingGet
     */
    public function get(string|Wallet $wallet): WithdrawalSettingGet
    {
        if (!$wallet instanceof Wallet) {
            $wallet = Wallet::findOrFail($wallet);
        }

        // Check Exchange API keys
        $exchangeKeys = ExchangeUserKey::where('user_id', $wallet->user_id)->get();

        if (!$exchangeKeys->isEmpty()) {
            $enableAutomaticExchange = $wallet->enable_automatic_exchange;
            $exchangeName = $wallet->exchange->name ?? null;

            $exchangeCurrencies = ExchangeWalletCurrency::select(
                'from_currency_id as fromCurrencyId',
                'to_currency_id as toCurrencyId'
            )
                ->where('wallet_id', $wallet->id)
                ->get()
                ->toArray();
        } else {
            $enableAutomaticExchange = null;
            $exchangeName = null;
            $exchangeCurrencies = null;
        }

        // Gets exchange cold wallets
        $exchangeColdWallet = ExchangeColdWallet::where('wallet_id', $wallet->id)->get();

        return new WithdrawalSettingGet([
            'address'                      => $wallet->address,
            'blockchain'                   => $wallet->blockchain->value,
            'enabled'                      => $wallet->withdrawal_enabled ?? false,
            'interval'                     => $wallet->withdrawal_interval ?? 0,
            'minBalance'                   => $wallet->withdrawal_min_balance ?? 0,
            'enableAutomaticExchange'      => $enableAutomaticExchange,
            'exchange'                     => $exchangeName,
            'exchangeCurrencies'           => $exchangeCurrencies,
            'exchangeColdWalletIsEnabled'  => false,
            'exchangeColdWalletAddresses'  => $exchangeColdWallet ?: null,
            'exchangeColdWalletMinBalance' => 0.00,
            'exchangeChain'                => null,
            'withdrawalIntervalCron'       => $wallet->withdrawal_interval_cron,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function updateExchange(Wallet $wallet, WalletSettingsRequest $request)
    {
        $exchange = ExchangeServiceEnum::tryFrom($request->input('exchange'));

        $wallet->updateOrFail([
            'withdrawal_enabled'        => $request->input('withdrawalEnabled'),
            'address'                   => $request->input('address'),
            'withdrawal_min_balance'    => $request->input('withdrawalMinBalance'),
            'enable_automatic_exchange' => $request->input('enableAutomaticExchange'),
            'exchange_id'               => $exchange->getId(),
            'withdrawal_interval_cron'  => $request->input('withdrawalIntervalCron'),
        ]);

        if (!$request->input('exchangeCurrenciesFrom')) {
            $wallet->exchangeWalletCurrency()->delete();
        } else {
            $wallet->exchangeWalletCurrency()->updateOrCreate([
                'from_currency_id' => $request->input('exchangeCurrenciesFrom')
            ], [
                'to_currency_id' => $request->input('exchangeCurrenciesTo'),
                'via'            => $request->input('exchangeCurrenciesTo') !== 'usdt' ? 'usdt' : null,
            ]);
        }

        $this->updateWalletInProcessing($wallet, $request->input('address'));

        $this->updateExchangeColdWallets($wallet, $request->input('exchangeColdWalletAddress'), $request->input('exchangeCurrenciesTo'));

    }

    private function updateExchangeColdWallets(Wallet $wallet, array $coldWalletData, ?string $currency = 'usdt'): void
    {
        if(empty($currency)) {
            $currency = 'usdt';
        }
        //fucking hack for camelCase convert to snake_case
        $collect = collect($coldWalletData)->map(function ($item) {
            $convertedKeys = array_map(function ($key) {
                return Str::snake($key);
            }, array_keys($item));
            return array_combine($convertedKeys, $item);
        });
        $collect = $collect->map(fn($item) => array_merge($item, ['currency' => $currency]));

        $wallet->exchangeColdWallets()->createUpdateOrDelete($collect, 'address');
    }

    public function storeColdWallet(ColdWalletDto $dto): void
    {
        ExchangeWithdrawalWallet::firstOrCreate([
            'exchange_id' => $dto->exchangeId,
            'user_id'     => $dto->userId,
            'chain'       => $dto->chain,
            'currency'    => $dto->currency,
            'address'    => $dto->address
        ], [
            'is_withdrawal_enable' => $dto->isWithdrawalEnable,
            'min_balance'          => $dto->minBalance,
        ]);
    }

}