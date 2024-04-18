<?php

namespace App\Providers;

use App\Http\Guards\StoreAuthGuard;
use App\Models\ExchangeWithdrawalWallet;
use App\Models\HotWallet;
use App\Models\Invite;
use App\Models\Store;
use App\Models\StoreApiKey;
use App\Models\Webhook;
use App\Models\WithdrawalWallet;
use App\Policies\ExchangeWithdrawalWalletPolicy;
use App\Policies\HotWalletPolicy;
use App\Policies\InvitePolicy;
use App\Policies\StoreApiKeyPolicy;
use App\Policies\StorePolicy;
use App\Policies\WebhookPolicy;
use App\Policies\WithdrawalWalletPolicy;
use App\Services\Processing\Contracts\OwnerContract;
use App\Services\User\UserService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Store::class                    => StorePolicy::class,
        Webhook::class                  => WebhookPolicy::class,
        StoreApiKey::class              => StoreApiKeyPolicy::class,
        Invite::class                   => InvitePolicy::class,
        WithdrawalWallet::class         => WithdrawalWalletPolicy::class,
        ExchangeWithdrawalWallet::class => ExchangeWithdrawalWalletPolicy::class,
        HotWallet::class                => HotWalletPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        $this->app->bind(Google2FA::class, fn () => new Google2FA());

        $this->app->bind(UserService::class, fn () => new UserService(
            $this->app->get(OwnerContract::class),
            $this->app->get('db.connection'),
            $this->app->get('hash'),
            $this->app->get('mailer'),
            $this->app->get(Google2FA::class)
        ));


        Auth::viaRequest('auth-api-key', $this->app->get(StoreAuthGuard::class));
    }
}
