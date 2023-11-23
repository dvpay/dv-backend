<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\CreateWithdrawalDto;
use App\Dto\WithdrawalListDto;
use App\Dto\WithdrawalSettingUpdate;
use App\Dto\WithdrawalStatsDto;
use App\Enums\ExchangeChainType;
use App\Enums\ExchangeService;
use App\Exceptions\ApiException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Wallet\CreateWalletRequest;
use App\Http\Requests\Wallet\UpdateWithdrawalSettingsRequest;
use App\Http\Requests\Wallet\WithdrawalRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Wallet\GetWithdrawalSettingsResource;
use App\Http\Resources\Wallet\WalletCollection;
use App\Http\Resources\Withdrawal\WithdrawalListCollection;
use App\Http\Resources\Withdrawal\WithdrawalStatsCollection;
use App\Models\Wallet;
use App\Services\Wallet\WalletCreator;
use App\Services\Wallet\WalletService;
use App\Services\Withdrawal\WithdrawalService;
use App\Services\Withdrawal\WithdrawalSettingService;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;


/**
 * WalletController
 */
class WalletController extends ApiController
{
    /**
     * @param WalletCreator $walletCreate
     * @param WalletService $walletService
     * @param WithdrawalService $withdrawalService
     * @param WithdrawalSettingService $withdrawalSettingService
     */
    public function __construct(
        private readonly WalletCreator            $walletCreate,
        private readonly WalletService            $walletService,
        private readonly WithdrawalService        $withdrawalService,
        private readonly WithdrawalSettingService $withdrawalSettingService
    )
    {
    }

    /**
     * @param CreateWalletRequest $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function create(Request $request): JsonResponse
    {
        $wallets = $this->walletCreate->createWallets($request->user());

        return (new WalletCollection($wallets))
            ->response();
    }

    /**
     * @param Request $request
     * @return WalletCollection
     */
    public function list(Request $request): WalletCollection
    {
        $user = $request->user();

        $wallets = $this->walletService->list($user);

        return new WalletCollection($wallets);
    }

    /**
     * @param Request $request
     * @param Wallet $wallet
     * @return GetWithdrawalSettingsResource
     */
    public function getSettings(Request $request, Wallet $wallet): GetWithdrawalSettingsResource
    {
        if ($request->user()->cannot('view', $wallet)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }
        $settings = $this->withdrawalSettingService->get($wallet);

        return new GetWithdrawalSettingsResource($settings);
    }

    /**
     * @param UpdateWithdrawalSettingsRequest $request
     * @param Wallet $wallet
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function updateSettings(UpdateWithdrawalSettingsRequest $request, Wallet $wallet): DefaultResponseResource
    {
        if ($request->user()->cannot('update', $wallet)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $input = $request->input();

        $dto = new WithdrawalSettingUpdate([
            'address'                      => $input['address'] ?? null,
            'blockchain'                   => $input['blockchain'] ?? null,
            'enabled'                      => $input['enabled'] ?? null,
            'minBalance'                   => $input['minBalance'] ?? null,
            'interval'                     => $input['interval'] ?? null,
            'enableAutomaticExchange'      => $input['enableAutomaticExchange'] ?? null,
            'exchange'                     => isset($input['exchange']) ? ExchangeService::tryFrom($input['exchange']) : null,
            'exchangeCurrencies'           => $input['exchangeCurrencies'],
            'exchangeColdWallet'           => $input['exchangeColdWallet'] ?? [],
            'exchangeColdWalletIsEnabled'  => $input['exchangeColdWalletIsEnabled'] ?? false,
            'exchangeColdWalletMinBalance' => $input['exchangeColdWalletMinBalance'] ?? null,
            'exchangeChain'                => $input['exchangeChain'] ?? ExchangeChainType::TRC20USDT->value,
            'withdrawalIntervalCron'       => $input['withdrawalIntervalCron'] ?? null,
        ]);

        $this->withdrawalSettingService->update($wallet->id, $dto);

        return new DefaultResponseResource([]);
    }

    /**
     * @param WithdrawalRequest $request
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function withdrawal(WithdrawalRequest $request)
    {
        $input = $request->input();
        $input['isManual'] = true;
        $input['user'] = $request->user();

        if ($request->user()->hasPermissionTo('transfer funds')) {
            throw new ApiException(__('Transfer disabled '), Response::HTTP_BAD_REQUEST);
        }

        $dto = new CreateWithdrawalDto($input);

        try {
            $response = $this->withdrawalService->withdrawal($dto);
            if ($response->getStatusCode() === Response::HTTP_OK) return new DefaultResponseResource([]);
        } catch (BadResponseException $exception) {
            $message = json_decode($exception->getResponse()->getBody()->getContents());
            throw new ApiException(__('Processing error ') . $message->error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param Request $request
     * @return WithdrawalListCollection
     */
    public function withdrawalList(Request $request): WithdrawalListCollection
    {
        $input = $request->input();

        $dto = new WithdrawalListDto([
            'page'          => $input['page'] ?? 1,
            'perPage'       => $input['perPage'] ?? 10,
            'sortField'     => $input['sortField'] ?? 'created_at',
            'sortDirection' => $input['sortDirection'] ?? 'desc',
            'user'          => $request->user(),
            'dateFrom'      => $input['dateFrom'] ?? '',
            'dateTo'        => $input['dateTo'] ?? '',
        ]);

        $list = $this->withdrawalService->withdrawalList($dto);

        return new WithdrawalListCollection($list);
    }

    public function transfer(Request $request, Authenticatable $user)
    {
        $permission = 'transfer funds';

        if ($request->input('status')) {
            $user->givePermissionTo($permission);
        } else {
            $user->revokePermissionTo($permission);
        }

        return new DefaultResponseResource([]);
    }

    public function withdrawalStats(Request $request, Authenticatable $user): DefaultResponseResource
    {
        return new DefaultResponseResource($this->withdrawalService->withdrawalStats($user));
    }

    public function withdrawalDates(Request $request, Authenticatable $user): WithdrawalStatsCollection
    {
        $input = $request->input();

        $dto = new WithdrawalStatsDto([
            'page'          => $input['page'] ?? 1,
            'perPage'       => $input['perPage'] ?? 10,
            'sortField'     => $input['sortField'] ?? 'date',
            'sortDirection' => $input['sortDirection'] ?? 'desc',
        ]);

        return new WithdrawalStatsCollection($this->withdrawalService->withdrawalDates($dto, $user));
    }

}
