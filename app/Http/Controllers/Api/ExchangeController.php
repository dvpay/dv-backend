<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\Exchange\ColdWalletDto;
use App\Dto\Exchange\UserPairsDto;
use App\Dto\ExchangeKeyAddDto;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Enums\PermissionsEnum;
use App\Exceptions\ApiException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Exchange\ExchangeAddKeysRequest;
use App\Http\Requests\Exchange\ExchangeRequest;
use App\Http\Requests\Exchange\ExchangeWithdrawalWalletStoreRequest;
use App\Http\Requests\Exchange\UserPairsRequest;
use App\Http\Requests\Exchange\UserPairsStoreRequest;
use App\Http\Requests\Exchange\WalletSettingsRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Exchange\ExchangeColdWalletWithdrawalCollection;
use App\Http\Resources\Exchange\ExchangeWithdrawalWalletCollection;
use App\Http\Resources\Exchange\UserPairsCollection;
use App\Models\ExchangeColdWalletWithdrawal;
use App\Models\ExchangeUserPairs;
use App\Models\ExchangeWithdrawalWallet;
use App\Models\Wallet;
use App\Services\Exchange\ExchangeManager;
use App\Services\Exchange\ExchangeService;
use App\Services\Withdrawal\WithdrawalSettingService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * ExchangeController
 */
class ExchangeController extends ApiController
{
    /**
     * @param ExchangeService $exchangeService
     */
    public function __construct(
        private readonly ExchangeService          $exchangeService,
        private readonly WithdrawalSettingService $withdrawalSettingService,
        private readonly ExchangeManager          $exchangeManager,
    )
    {
    }

    /**
     * @param Request $request
     *
     * @return DefaultResponseResource
     */
    public function getKeys(Request $request)
    {
        $user = $request->user();

        $keys = $this->exchangeService->getKeys($user);

        return new DefaultResponseResource($keys);
    }

    /**
     * @param Request $request
     *
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function addKeys(ExchangeAddKeysRequest $request)
    {
        $user = $request->user();
        $input = $request->input();

        $dto = new ExchangeKeyAddDto([
            'exchange' => $input['exchange'],
            'keys'     => $input['keys'],
            'user'     => $user,
        ]);

        $this->exchangeService->addKey($dto);

        return new DefaultResponseResource([]);
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    public function testConnection(Request $request, Authenticatable $user)
    {
        $exchangeService = $this->exchangeManager
            ->setUser($user)
            ->driver($request->input('exchange'));


        if (!$exchangeService->testConnection()) {
            throw new ApiException('Test connection - failed.', Response::HTTP_BAD_REQUEST);
        }

        return new DefaultResponseResource([]);
    }

    private function getService(string $exchange, Authenticatable $user, ExchangeManager $exchangeManager)
    {
        return $exchangeManager->setUser($user)->driver($exchange);
    }

    public function depositAddresses(ExchangeRequest $request, Authenticatable $user, ExchangeManager $exchangeManager)
    {
        $service = $this->getService($request->input('exchange'), $user, $exchangeManager);
        $depositAddress = $service->loadDepositAddress();

        return new DefaultResponseResource($depositAddress);
    }

    public function withdrawalAddresses(ExchangeRequest $request, Authenticatable $user, ExchangeManager $exchangeManager)
    {
        $service = $this->getService($request->input('exchange'), $user, $exchangeManager);
        $withdrawalAddress = $service->loadWithdrawalAddress();

        return new DefaultResponseResource($withdrawalAddress);
    }

    public function symbols()
    {
        $symbolsByCurrency = [
            'btc'  => [
                [
                    "fromCurrencyId" => "btc",
                    "toCurrencyId"   => "usdt",
                    "symbol"         => "btcusdt"
                ],
                [
                    "fromCurrencyId" => "btc",
                    "toCurrencyId"   => "eth",
                    "symbol"         => "btceth"
                ],
            ],
            'usdt' => [
                [
                    "fromCurrencyId" => "usdt",
                    "toCurrencyId"   => "btc",
                    "symbol"         => "btcusdt"
                ],
                [
                    "fromCurrencyId" => "usdt",
                    "toCurrencyId"   => "eth",
                    "symbol"         => "ethusdt"
                ],
            ]
        ];

        return new DefaultResponseResource($symbolsByCurrency);
    }

    public function symbolFromExchange(ExchangeRequest $request, Authenticatable $user, ExchangeManager $exchangeManager)
    {
        $service = $this->getService($request->input('exchange'), $user, $exchangeManager);
        return DefaultResponseResource::make($service->loadExchangeSymbols());
    }

    #[Deprecated]
    public function saveExchangeUserPairs(UserPairsStoreRequest $request, Authenticatable $user, ExchangeManager $exchangeManager)
    {
        $exchange = ExchangeServiceEnum::tryFrom($request->input('exchange'));

        $service = $this->getService($request->input('exchange'), $user, $exchangeManager);
        $dto = new UserPairsDto([
            'exchangeId'   => $exchange->getId(),
            'userId'       => $user->id,
            'currencyFrom' => $request->input('currencyFrom'),
            'currencyTo'   => $request->input('currencyTo'),
            'via'          => $request->input('currencyTo') !== 'usdt' ? 'usdt' : null,
            'symbol'       => $request->input('symbol'),
        ]);

        $service->saveExchangeUserPairs($dto);
        return DefaultResponseResource::make([]);
    }

    public function saveUserPairs(UserPairsRequest $request, Authenticatable $user, ExchangeManager $exchangeManager)
    {
        $exchange = ExchangeServiceEnum::tryFrom($request->input('exchange'));
        $service = $this->getService($request->input('exchange'), $user, $exchangeManager);

        $service->deleteExchangeUserPairs($exchange->getId());

        foreach ($request->input('directions') as $direction) {
            $dto = new UserPairsDto([
                'exchangeId'   => $exchange->getId(),
                'userId'       => $user->id,
                'currencyFrom' => $direction['currencyFrom'],
                'currencyTo'   => $direction['currencyTo'],
                'type'         => $direction['type'],
                'symbol'       => $direction['symbol'],
            ]);
            $service->saveExchangeUserPairs($dto);
        };

        return DefaultResponseResource::make([]);
    }

    public function getExchangeUserPairs(ExchangeRequest $request, Authenticatable $user)
    {
        $exchange = ExchangeServiceEnum::tryFrom($request->input('exchange'));

        $exchangeUserPairs = ExchangeUserPairs::where('exchange_id', $exchange->getId())
            ->where('user_id', $user->id)
            ->get();

        return UserPairsCollection::make($exchangeUserPairs);
    }

    /**
     * @throws Throwable
     */
    public function updateExchangeWalletSetting(WalletSettingsRequest $request, Authenticatable $user, Wallet $wallet): DefaultResponseResource
    {
        if ($user->cannot('update', $wallet)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $this->withdrawalSettingService->updateExchange($wallet, $request);
        return DefaultResponseResource::make([]);
    }

    public function getColdWallets(Authenticatable $user): ExchangeWithdrawalWalletCollection
    {
        $wallets = ExchangeWithdrawalWallet::where('user_id', $user->id)
            ->get();

        return ExchangeWithdrawalWalletCollection::make($wallets);
    }

    public function storeColdWallets(ExchangeWithdrawalWalletStoreRequest $request, Authenticatable $user): DefaultResponseResource
    {
        $exchange = ExchangeServiceEnum::tryFrom($request->input('exchange'));

        $dto = new ColdWalletDto([
            'exchangeId'         => $exchange->getId(),
            'userId'             => $user->id,
            'isWithdrawalEnable' => $request->input('is_withdrawal_enable'),
            'minBalance'         => $request->input('min_balance'),
            'chain'              => $request->input('chain'),
            'currency'           => $request->input('currency'),
            'address'            => $request->input('address'),
        ]);

        $this->withdrawalSettingService->storeColdWallet($dto);
        return DefaultResponseResource::make([]);
    }

    public function deleteColdWallets(ExchangeWithdrawalWallet $coldWallet, Authenticatable $user): DefaultResponseResource
    {
        if ($user->cannot('delete', $coldWallet)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $coldWallet->delete();
        return DefaultResponseResource::make([]);
    }

    public function updateStatus(Request $request, Authenticatable $user)
    {
        ExchangeWithdrawalWallet::where('user_id', $user->id)
            ->update(['is_withdrawal_enable' => $request->input('status')]);

        return DefaultResponseResource::make([]);
    }

    public function withdrawalHistory(Request $request, Authenticatable $user)
    {
        $wallets = ExchangeWithdrawalWallet::where('user_id', $user->id)
            ->get('id');

        $withdrawal = ExchangeColdWalletWithdrawal::whereIn('exchange_cold_wallet_id', $wallets->toArray())
            ->paginate($request->input('perPage'));

        return ExchangeColdWalletWithdrawalCollection::make($withdrawal);
    }

    public function updateExchangeStatus(Request $request, Authenticatable $user)
    {
        $permission = PermissionsEnum::ExchangeStop;

        if ($request->input('status')) {
            $user->givePermissionTo($permission->value);
        } else {
            $user->revokePermissionTo($permission->value);
        }

        return new DefaultResponseResource([]);
    }
}
