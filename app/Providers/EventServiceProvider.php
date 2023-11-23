<?php

namespace App\Providers;

use App\Events\InvoiceAddressUpdateEvent;
use App\Events\InvoiceCreatedEvent;
use App\Events\InvoiceStatusUpdatedEvent;
use App\Events\TransactionCreatedEvent;
use App\Events\WebhookIsSentEvent;
use App\Listeners\DropUnconfirmedTransactionListener;
use App\Listeners\InvoiceAddressUpdateListener;
use App\Listeners\InvoiceStatusHistoryListener;
use App\Listeners\SendEmailListener;
use App\Listeners\SendTransactionTelegramNotificationListener;
use App\Listeners\SendWebhookListener;
use App\Listeners\SendWebhookTelegramNotificationListener;
use App\Listeners\StoreFirstInvoiceStatusListener;
use App\Listeners\WebhookHistoryListener;
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
            SendWebhookListener::class,
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
        ],
        InvoiceAddressUpdateEvent::class => [
            InvoiceAddressUpdateListener::class,
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
