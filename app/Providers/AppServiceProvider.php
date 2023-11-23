<?php

namespace App\Providers;

use App\Console\Commands\ExplorerServiceStatusCheck;
use App\Console\Commands\NodeStatusCheck;
use App\Console\Commands\NodeVersionControl;
use App\Console\Commands\TelegramWebhookSet;
use App\Enums\WithdrawalRuleType;
use App\Http\Controllers\Api\DictionaryController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Middleware\CheckSign;
use App\Jobs\WebhookJob;
use App\Listeners\SendWebhookListener;
use App\Repositories\CurrencyRepository;
use App\Repositories\StoreRepository;
use App\Services\ApiKey\ApiKeyService;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateCheck;
use App\Services\Currency\CurrencyRateService;
use App\Services\Currency\CurrencyStore;
use App\Services\Dictionary\DictionaryService;
use App\Services\HotWallet\HotWalletService;
use App\Services\HotWallet\HotWalletServiceInterface;
use App\Services\Invoice\InvoiceAddressCreator;
use App\Services\Invoice\InvoiceAddressService;
use App\Services\Invoice\InvoiceAddressServiceNew;
use App\Services\Invoice\InvoiceCreator;
use App\Services\Invoice\InvoiceService;
use App\Services\Processing\Contracts\AddressContract;
use App\Services\Processing\ProcessingCallbackHandler;
use App\Services\Report\ReportService;
use App\Services\Store\StoreService;
use App\Services\Telegram\TelegramService;
use App\Services\Withdrawal\Rules\Interval;
use App\Services\Withdrawal\Rules\MinBalance;
use App\Services\Withdrawal\TransferRuleManager;
use App\Services\Withdrawal\WithdrawalRuleManager;
use App\Services\Webhook\WebhookDataService;
use App\Services\Webhook\WebhookManager;
use App\Services\Webhook\WebhookSender;
use App\Services\Withdrawal\WithdrawalSettingService;
use App\Support\Macros\CreateUpdateOrDelete;
use GuzzleHttp\Client;
use Illuminate\Cache\Repository;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\ServiceProvider;
use TelegramBot\Api\BotApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        HasMany::macro('createUpdateOrDelete', function (iterable $records, string $recordKeyName) {
            $hasMany = $this;
            return (new CreateUpdateOrDelete($hasMany, $records, $recordKeyName))();
        });

        $this->app->bind(DictionaryController::class, fn() => new DictionaryController(
            $this->app->get(DictionaryService::class),
            $this->app->get('cache.store')
        ));

        $this->app->bind(ApiKeyService::class, fn() => new ApiKeyService(
            config('setting.salt')
        ));

        $this->app->bind(CurrencyStore::class, fn() => new CurrencyStore(
            $this->app->get(Repository::class),
            config('setting.cache_lifetime')
        ));

        $this->app->bind(CurrencyRateCheck::class, fn() => new CurrencyRateCheck(
            $this->app->get(CurrencyStore::class),
            config('setting.max_rate_difference')
        ));

        $this->app->bind(InvoiceAddressCreator::class, fn() => new InvoiceAddressCreator(
            $this->app->get(AddressContract::class),
            $this->app->get(CurrencyRateService::class),
            $this->app->get(CurrencyConversion::class),
        ));

        $this->app->bind(InvoiceController::class, fn() => new InvoiceController(
            $this->app->get(InvoiceService::class),
            $this->app->get(InvoiceAddressService::class),
            $this->app->get(InvoiceAddressServiceNew::class),
            $this->app->get(InvoiceCreator::class),
            $this->app->get(InvoiceAddressCreator::class),
            $this->app->get(StoreRepository::class),
            $this->app->get(CurrencyConversion::class),
            $this->app->get(CurrencyRepository::class),
            $this->app->get(Repository::class),
            config('setting.payment_form_url'),
            config('setting.disabled_blockchains', [])
        ));

        $this->app->bind(StoreController::class, fn() => new StoreController(
            $this->app->get(StoreService::class),
            $this->app->get(CurrencyRateService::class),
            $this->app->get(CurrencyConversion::class),
            $this->app->get(CurrencyRepository::class),
            config('setting.invoice_lifetime')
        ));

        $this->app->bindMethod([WebhookJob::class, 'handle'], function ($job, $app) {
            return $job->handle(
                $app->make(WebhookManager::class),
                $app->make(WebhookDataService::class),
                $app->make(WebhookSender::class),
                config('setting.repeat_job_timeout')
            );
        });

        $this->app->bind(ProcessingCallbackHandler::class, fn() => new ProcessingCallbackHandler(
            $this->app->get(CurrencyConversion::class),
            $this->app->get(Connection::class),
            config('processing.min_transaction_confirmations')
        ));

        $this->app->bind(CheckSign::class, fn() => new CheckSign(
            config('processing.client.webhookKey')
        ));

        $this->app->bind(TelegramService::class, fn() => new TelegramService(
            new BotApi(config('telegram.token')),
            $this->app->get(ReportService::class),
            config('telegram.bot'),
            config('telegram.token'),
            config('app.url')
        ));

        $this->app->bind(WebhookSender::class, fn() => new WebhookSender(
            $this->app->get(Client::class),
            config('setting.webhook_timeout'),
        ));

        $this->app->bind(WithdrawalRuleManager::class, fn() => new WithdrawalRuleManager(
            $this->app->get(WithdrawalSettingService::class),
            [
                WithdrawalRuleType::Interval->value => $this->app->get(Interval::class),
                WithdrawalRuleType::BalanceLimit->value => $this->app->get(MinBalance::class),
            ],
        ));

        $this->app->bind(ExplorerServiceStatusCheck::class, fn() => new ExplorerServiceStatusCheck(
            $this->app->get(Client::class),
            config('explorer.bitcoinExplorerUrl', ''),
            config('explorer.tronExplorerUrl', ''),
        ));

        $this->app->bind(NodeStatusCheck::class, fn() => new NodeStatusCheck(
            $this->app->get(Client::class),
            config('node.url'),
        ));

        $this->app->bind(NodeVersionControl::class, fn() => new NodeVersionControl(
            $this->app->get(Client::class),
            config('node.url'),
        ));

        $this->app->bind(TelegramWebhookSet::class, fn() => new TelegramWebhookSet(
            $this->app->get(TelegramService::class),
            config('telegram.webhook_url')
        ));

        $this->app->bind(SendWebhookListener::class, fn() => new SendWebhookListener(
            config('setting.new_send_webhook_logic', false)
        ));

        $this->app->bind(HotWalletServiceInterface::class, HotWalletService::class);
    }
}
