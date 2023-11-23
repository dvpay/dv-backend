<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\CurrencyId;
use App\Enums\CurrencySymbol;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\RateSource;
use App\Jobs\HeartbeatStatusJob;
use App\Models\ExchangeColdWalletWithdrawal;
use App\Models\ExchangeDictionary;
use App\Models\ExchangeTransaction;
use App\Models\ExchangeWalletCurrency;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Models\Wallet;
use App\Services\Currency\CurrencyRateService;
use App\Services\Exchange\ExchangeService;
use App\Services\Exchange\HuobiExchangeService;
use App\Services\Exchange\ExchangeManagerInterface;
use App\Services\Huobi\HuobiService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

/** TODO works on the word of God
 * ExchangeWithdrawal
 * need refactoring this command
 */
class ExchangeWithdrawal extends Command
{
    private ServiceLogLaunch $serviceLogLaunch;
    private Service $service;

    /**
     * @param ExchangeService $exchangeService
     * @param CurrencyRateService $currencyRateService
     */
    public function __construct(
        private readonly ExchangeService          $exchangeService,
        private readonly CurrencyRateService      $currencyRateService,
        private readonly ExchangeManagerInterface $exchangeManager,
    )
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "exchange:withdrawal";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run exchange on external resource.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->initMonitor();

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start Exchange',
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        try {
            $time = time();

            $wallets = Wallet::where('enable_automatic_exchange', true)->get();
            foreach ($wallets as $wallet) {
                if ($wallet->exchange_id == ExchangeServiceEnum::Huobi->getId()) {
                    $exchange = ExchangeServiceEnum::tryFrom('huobi');
                    $exchangeService = $this->exchangeManager->make($exchange, $wallet->user);
                    $this->exchangeHuobi($wallet, $exchangeService);
                    $this->withdrawHuobi($wallet);
                }
            }

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            $this->info('The command was successful! ' . time() - $time . ' s.');
        } catch (Throwable $e) {
            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Down,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            Log::channel('exchangeLog')
                ->error(
                    'ExchangeWithdrawal',
                    [$e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine()]
                );

            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    private function exchangeHuobi(Wallet $wallet, HuobiExchangeService $exchangeService)
    {
        $huobiKeys = $this->exchangeService->getHuobiKeys($wallet->user_id);

        if (!isset($huobiKeys['accessKey']) || !isset($huobiKeys['secretKey'])) {
            return;
        }

        $huobiService = new HuobiService(
            $huobiKeys['accessKey'],
            $huobiKeys['secretKey'],
            $wallet->user
        );

        $result = $huobiService->getAccountAccounts();

        Log::channel('exchangeLog')->error('ExchangeWithdrawal (accounts)', [$result]);

        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'ExchangeWithdrawal (accounts) :reasonText.',
            messageVariable: ['reasonText' => $result],
            serviceLogLaunch: $this->serviceLogLaunch,
        );

        if (!isset($result->data)) {
            return;
        }

        foreach ($result->data as $account) {
            if ($account->state == 'working' && $account->type == 'spot') {
                $accountId = $account->id;
                break;
            }
        }

        if (empty($accountId)) {
            return;
        }

        $exchangeWalletCurrencies = ExchangeWalletCurrency::where('wallet_id', $wallet->id)->get();
        foreach ($exchangeWalletCurrencies as $exchangeWalletCurrency) {
            $exchangeDictionary = ExchangeDictionary::where([
                ['from_currency_id', CurrencyId::getToken($exchangeWalletCurrency->from_currency_id)->value],
                ['to_currency_id', CurrencyId::getToken($exchangeWalletCurrency->to_currency_id)->value],
            ])->first();

            $from = $exchangeWalletCurrency->from_currency_id;
            $to = $exchangeWalletCurrency->to_currency_id;
            $via = $exchangeWalletCurrency->via;

            // Check balance
            $balances = $huobiService->getBalance($accountId);

            foreach ($balances->data->list as $balance) {
                if ($balance->currency != $from) {
                    continue;
                }

                if ($balance->balance == 0 || $balance->balance < $exchangeDictionary->min_quantity) {
                    continue;
                }

                Log::channel('exchangeLog')->error('ExchangeWithdrawal (balances)', [$balance]);

                HeartbeatStatusJob::dispatch(
                    service: $this->service,
                    status: HeartbeatStatus::InProgress,
                    message: 'Get balance from huobi :reasonText.',
                    messageVariable: ['reasonText' => $balance],
                    serviceLogLaunch: $this->serviceLogLaunch,
                );

                if ($balance->currency === 'btc') {
                    $amountOriginal = floor($balance->balance * 1000000) / 1000000;
                } else {
                    $amountOriginal = (float)$balance->balance;
                }

                $decimals = $exchangeDictionary->decimals ?? 0;
                $amount = number_format((float)$amountOriginal, $decimals, '.', '');

                if ($via) {
                    Log::channel('exchangeLog')->error('ExchangeWithdrawal via:' . $via);
                    $result = $huobiService->placeOrder(
                        (string)$accountId,
                        $from . $via,
                        'sell-market',
                        $amount
                    );
                    if ($result->status != 'ok') {
                        Log::channel('exchangeLog')->error('ExchangeWithdrawal via result:', [$result]);
                        continue;
                    }

                    $usdtAmount = number_format($exchangeService->calculateUsdt($from . $via, $amountOriginal), $decimals, '.', '');

                    Log::channel('exchangeLog')->error('ExchangeWithdrawal via:' . $to . $via);
                    $result = $huobiService->placeOrder(
                        (string)$accountId,
                        $to . $via,
                        'buy-market',
                        $usdtAmount
                    );
                } else {
                    $result = $huobiService->placeOrder(
                        (string)$accountId,
                        $from . $to,
                        'sell-market',
                        $amount
                    );
                }


                Log::channel('exchangeLog')->error('ExchangeWithdrawal (placeOrder)', [$result]);

                HeartbeatStatusJob::dispatch(
                    service: $this->service,
                    status: HeartbeatStatus::InProgress,
                    message: 'Place Order :reasonText.',
                    messageVariable: ['reasonText' => $result],
                    serviceLogLaunch: $this->serviceLogLaunch,
                );

                if ($result->status != 'ok') {
                    continue;
                }

                $newBalances = $huobiService->getBalance($accountId);
                foreach ($newBalances->data->list as $newBalance) {
                    if ($newBalance->currency == $from) {
                        $amount = number_format($balance->balance - $newBalance->balance, 8, '.', ' ');
                        $leftBalance = $newBalance->balance;
                        break;
                    }
                }

                // Save exchange_transaction
                $exchangeTransaction = new ExchangeTransaction([
                    'user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id,
                    'from_currency_id' => CurrencyId::getToken($exchangeWalletCurrency->from_currency_id)->value,
                    'to_currency_id' => CurrencyId::getToken($exchangeWalletCurrency->to_currency_id)->value,
                    'amount' => $amount,
                    'amount_usd' => $this->inUsd($from, $amount),
                    'left_amount' => $leftBalance
                ]);
                $exchangeTransaction->saveOrFail();

                HeartbeatStatusJob::dispatch(
                    service: $this->service,
                    status: HeartbeatStatus::InProgress,
                    message: 'Success exchange :reasonText.',
                    messageVariable: ['reasonText' => $exchangeTransaction],
                    serviceLogLaunch: $this->serviceLogLaunch,
                );
            }
        }
    }

    /**
     * Withdraws funds to a cold wallet.
     *
     * @param Wallet $wallet
     *
     * @return bool
     * @throws GuzzleException
     * @throws Throwable
     */
    private function withdrawHuobi(Wallet $wallet): bool
    {
        $huobiKeys = $this->exchangeService->getHuobiKeys($wallet->user_id);

        if (empty($huobiKeys['accessKey']) || empty($huobiKeys['secretKey'])) {
            return false;
        }

        $exchangeColdWallet = $wallet->exchangeColdWallets()->inRandomOrder()->first();

        if (empty($exchangeColdWallet) || !$exchangeColdWallet->isWithdrawalEnabled()) {
            return false;
        }

        Log::channel('exchangeLog')->info('Trying to withdraw', [$wallet]);

        $huobiService = new HuobiService(
            $huobiKeys['accessKey'],
            $huobiKeys['secretKey'],
            $wallet->user
        );

        $accounts = $huobiService->getAccountAccounts();

        if (!isset($accounts->data)) {
            return false;
        }

        foreach ($accounts->data as $account) {

            if ($account->state == 'working' && $account->type == 'spot') {
                $accountId = $account->id;
                break;
            }

        }

        if (empty($accountId)) {
            return false;
        }

        $balances = $huobiService->getBalance($accountId);

        $withdrawal = null;

        foreach ($balances->data->list as $balance) {
            if ($balance->type === 'trade' && $balance->balance > 0 && $balance->currency === $exchangeColdWallet->currency && $balance->balance > $exchangeColdWallet->withdrawal_min_balance) {
                $withdrawal['balance'] = floor((float)$balance->balance - 10);
                $withdrawal['available'] = floor((float)$balance->available - 10);

                Log::channel('exchangeLog')->info('Balance to withdraw', [$balance]);

                break;
            }
        }

        if (empty($withdrawal)) {
            Log::channel('exchangeLog')->info('Nothing to withdraw');

            return false;
        }

        // Places an order

        $withdrawalAmount = (string)$withdrawal['available'];
        $withdrawalFee = 0;

        Log::channel('exchangeLog')->info('Balances to order', ['amount' => $withdrawalAmount, 'fee' => $withdrawalFee]);

        $result = $huobiService->createWithdrawal((string)$exchangeColdWallet->address, $withdrawalAmount, $exchangeColdWallet->currency, $withdrawalFee, '', $exchangeColdWallet->chain);

        if (empty($result->status) || $result->status != 'ok') {
            Log::channel('exchangeLog')->error('Withdrawal failed', [$result]);

            return false;
        }

        Log::channel('exchangeLog')->info('Withdrawal completed', [$result]);

        $exchangeColdWalletWithdrawal = new ExchangeColdWalletWithdrawal([
            'exchange_cold_wallet_id' => $exchangeColdWallet->id,
            'exchange_id' => ExchangeServiceEnum::Huobi->getId(),
            'address' => (string)$exchangeColdWallet->address,
            'amount' => (string)$withdrawal['available'],
        ]);
        $exchangeColdWalletWithdrawal->saveOrFail();

        return true;
    }

    /**
     * @param string $from
     * @param string $amount
     * @param bool $reverseRate
     *
     * @return string
     */
    public function inUsd(string $from, string $amount, bool $reverseRate = false): string
    {
        $from = CurrencySymbol::tryFrom(strtoupper($from));

        return $this->currencyRateService->inUsd(
            RateSource::Binance,
            $from,
            CurrencySymbol::USDT,
            $amount,
            true
        );
    }

    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::CronExchangeWithdrawal)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status' => HeartbeatStatus::InProgress
        ]);
    }
}