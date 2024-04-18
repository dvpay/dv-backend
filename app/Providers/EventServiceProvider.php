<?php

namespace App\Providers;

use App\Events\InvoiceAddressUpdateEvent;
use App\Events\InvoiceCreatedEvent;
use App\Events\InvoiceStatusUpdatedEvent;
use App\Events\SettingUpdatedEvent;
use App\Events\PaymentReceivedEvent;
use App\Events\TransactionCreatedEvent;
use App\Events\UnconfirmedTransactionCreatedEvent;
use App\Events\WebhookIsSentEvent;
use App\Listeners\AppUrlSettingUpdatedListener;
use App\Listeners\DropUnconfirmedTransactionListener;
use App\Listeners\InvoiceAddressUpdateListener;
use App\Listeners\InvoiceStatusHistoryListener;
use App\Listeners\NewWebhookHistoryListener;
use App\Listeners\SendEmailListener;
use App\Listeners\SendTransactionTelegramNotificationListener;
use App\Listeners\SendWebhookTelegramNotificationListener;
use App\Listeners\SendWebhookToStoreListener;
use App\Listeners\StoreFirstInvoiceStatusListener;
use App\Listeners\UpdateHotWalletBalanceTransactionListener;
use App\Listeners\WebhookHistoryListener;
use App\Models\User;
use App\WebhookServer\Events\WebhookCallFailedEvent;
use App\WebhookServer\Events\WebhookCallSucceededEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * EventServiceProvider
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class                => [
            SendEmailVerificationNotification::class,
        ],
        InvoiceCreatedEvent::class       => [
            StoreFirstInvoiceStatusListener::class
        ],
        InvoiceStatusUpdatedEvent::class => [
            //SendWebhookListener::class,
            InvoiceStatusHistoryListener::class,
            SendEmailListener::class,
        ],
        WebhookIsSentEvent::class        => [
            WebhookHistoryListener::class,
            SendWebhookTelegramNotificationListener::class,
        ],
        TransactionCreatedEvent::class   => [
            SendTransactionTelegramNotificationListener::class,
            DropUnconfirmedTransactionListener::class,
            UpdateHotWalletBalanceTransactionListener::class,
        ],
        InvoiceAddressUpdateEvent::class => [
            InvoiceAddressUpdateListener::class,
        ],
        SettingUpdatedEvent::class => [
            AppUrlSettingUpdatedListener::class,
        ],
        PaymentReceivedEvent::class => [
            SendWebhookToStoreListener::class
        ],

        UnconfirmedTransactionCreatedEvent::class => [
            SendWebhookToStoreListener::class
        ],

        WebhookCallFailedEvent::class => [
            NewWebhookHistoryListener::class
        ],

        WebhookCallSucceededEvent::class => [
            NewWebhookHistoryListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
