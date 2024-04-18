<?php

namespace App\Http\Controllers\Api;

use App\Enums\CurrencyId;
use App\Enums\ExchangeService;
use App\Enums\PermissionsEnum;
use App\Enums\WithdrawalRuleType;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\WithdrawalRequest;
use App\Http\Requests\Withdrawal\WithdrawalWalletUpdateRequest;
use App\Http\Requests\Withdrawal\WithdrawalWalletUpdateWithdrawalRulesRequest;
use App\Http\Requests\Withdrawal\WithdrawalWalletWithdrawFromProcessingRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Withdrawal\WithdrawalWalletCollection;
use App\Http\Resources\Withdrawal\WithdrawalWalletResource;
use App\Models\WithdrawalWallet;
use App\Services\Processing\Contracts\OwnerContract;
use App\Services\WithdrawalWallet\WithdrawalWalletService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class WithdrawalWalletController extends Controller
{
    public function __construct(
        public readonly WithdrawalWalletService $withdrawalWalletService,
        public readonly OwnerContract           $ownerContract,
    )
    {
    }

    public function index(Authenticatable $user)
    {
        if ($user->withdrawalWallets->isEmpty()) {
            $this->withdrawalWalletService->createWallets($user);
        }

        $wallets = WithdrawalWallet::where('user_id', $user->id)
            ->with('address')
            ->get();

        return WithdrawalWalletCollection::make($wallets);
    }

    public function show(WithdrawalWallet $withdrawalWallet)
    {
        $wallet = $withdrawalWallet->load(['address', 'exchange']);

        return WithdrawalWalletResource::make($wallet);
    }

    /**
     * //todo transfer logic on service
     * @throws \Throwable
     */
    #[OA\Put(
        path: '/withdrawal-wallet/{withdrawalWallet}',
        summary: 'Update Withdrawal Wallet',
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id",
                            description: "Withdrawal Wallet",
                            type: "string",
                            example: "84ffc844-b4c6-4d67-802b-e4516039038c",
                        ),
                        new OA\Property(property: "addressType",
                            description: "Address type",
                            type: "string",
                            example: "interval"
                        ),
                        new OA\Property(property: "exchangeSlug",
                            description: "Exchange slug",
                            type: "string",
                            example: null
                        ),
                        new OA\Property(property: "validateCode",
                            description: "2FA code",
                            type: "string",
                            example: "111111"
                        ),
                        new OA\Property(property: "withdrawalEnabled",
                            description: "Withdrawal enabled",
                            type: "boolean",
                            example: true
                        ),
                        new OA\Property(property: "withdrawalInterval",
                            description: "Withdrawal rules",
                            type: "string",
                            example: "EveryOneMin"
                        ),
                        new OA\Property(property: "withdrawalMinBalance",
                            description: "Withdrawal minimal balance",
                            type: "integer",
                            example: 10
                        ),
                    ],
                    type: "object"
                )
            ]
        ),
        tags: ['Withdrawal Wallet'],
        parameters: [
            new OA\Parameter(name: 'withdrawalWallet', description: 'Withdrawal Wallet', in: 'path', required: true,
                schema: new OA\Schema(type: 'string', example: '84ffc844-b4c6-4d67-802b-e4516039038c')),
        ],
        responses: [
            new OA\Response(response: 200, description: "2FA code sent | Withdrawal Wallet Updated", content: new OA\JsonContent(
                examples: [
                    new OA\Examples(
                        example: '{"result":{"codeSend":true},"errors":[]}',
                        summary: "2FA code sent",
                        value: '{"result":{"codeSend":true},"errors":[]}',

                    ),
                    new OA\Examples(
                        example: '{"result":[],"errors":[]}',
                        summary: "Withdrawal Wallet Updated",
                        value: '{"result":[],"errors":[]}',
                    ),
                ]

            )),
            new OA\Response(response: 422, description: "Invalid input data", content: new OA\JsonContent(
                example: '{"message":"ThedssdfmustbeavalidTRC-20address.","errors":{"address.2":["ThedssdfmustbeavalidTRC-20address."]}}'
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
        ]
    )]
    public function update(WithdrawalWalletUpdateRequest $request, WithdrawalWallet $withdrawalWallet)
    {
        if (!$request->has('validateCode')) {
            $this->ownerContract->attachColdWalletWithAddress(
                blockchain: $withdrawalWallet->blockchain,
                owner: $withdrawalWallet->user->processing_owner_id,
                address: $withdrawalWallet->address->pluck('address')->toArray(),
            );
            return DefaultResponseResource::make(['codeSend' => true]);
        }

        DB::transaction(function () use ($withdrawalWallet, $request) {
            $withdrawalWallet->updateOrFail([
                'exchange_id'            => $request->input('exchangeSlug') ? ExchangeService::tryFrom($request->input('exchangeSlug'))->getId() : null,
                'type'                   => $request->input('addressType', $withdrawalWallet->type ?? WithdrawalRuleType::Manual),
                'withdrawal_enabled'     => $request->input('withdrawalEnabled', $withdrawalWallet->withdrawal_enabled),
                'withdrawal_min_balance' => $request->input('withdrawalMinBalance', $withdrawalWallet->withdrawal_min_balance),
                'withdrawal_interval'    => $request->input('withdrawalInterval', $withdrawalWallet->withdrawal_interval),
            ]);
            $withdrawalWallet->address()->delete();
            foreach ($request->input('address') as $address) {
                $withdrawalWallet->address()->restoreOrCreate(
                    ['address' => $address],
                    ['withdrawal_wallet_id' => $withdrawalWallet->id]
                );
            }
            $this->ownerContract->attachColdWalletWithAddress(
                blockchain: $withdrawalWallet->blockchain,
                owner: $withdrawalWallet->user->processing_owner_id,
                address: $withdrawalWallet->address->pluck('address')->toArray(),
                validateCode: $request->input('validateCode')
            );
        });

        return DefaultResponseResource::make([]);
    }

    #[OA\Put(
        path: '/withdrawal-wallet/{withdrawalWallet}/withdrawal-rules',
        summary: 'Update Withdrawal Wallet Rules',
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "withdrawalInterval",
                            description: "Withdrawal rules",
                            type: "string",
                            example: "EveryOneMin"
                        ),
                        new OA\Property(property: "withdrawalMinBalance",
                            description: "Withdrawal minimal balance",
                            type: "integer",
                            example: 10
                        ),
                    ],
                    type: "object"
                )
            ]
        ),
        tags: ['Withdrawal Wallet'],
        parameters: [
            new OA\Parameter(name: 'withdrawalWallet', description: 'Withdrawal Wallet', in: 'path', required: true,
                schema: new OA\Schema(type: 'string', example: '84ffc844-b4c6-4d67-802b-e4516039038c')),
        ],
        responses: [
            new OA\Response(response: 200, description: "2FA code sent | Withdrawal Wallet Updated", content: new OA\JsonContent(
                example: '{"result":[],"errors":[]}'
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
        ]
    )]
    public function updateWithdrawalRules(WithdrawalWalletUpdateWithdrawalRulesRequest $request, WithdrawalWallet $withdrawalWallet)
    {

        $withdrawalWallet->updateOrFail([
            'withdrawal_min_balance' => $request->input('withdrawalMinBalance'),
            'withdrawal_interval'    => $request->input('withdrawalInterval'),
        ]);

        return DefaultResponseResource::make([]);
    }

    public function withdrawal(WithdrawalRequest $request, Authenticatable $user)
    {
        if ($request->user()->hasPermissionTo('transfer funds')) {
            throw new ApiException(__('Transfer disabled '), Response::HTTP_BAD_REQUEST);
        }

        $this->withdrawalWalletService->withdrawal($user, $request->input('chain'));

        return DefaultResponseResource::make([]);
    }

    #TODO: Create separate Request with validation
    public function withdrawalFromAddress(Request $request, Authenticatable $user)
    {
        $this->withdrawalWalletService->withdrawalFromAddress($user, $request->input('currencyId'), $request->input('addressFrom'));

        return DefaultResponseResource::make([]);
    }

    #[OA\Post(
        path: '/withdrawal-wallet/withdrawal-from-processing-wallet',
        summary: 'Withdraw From Processing Wallet',
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "currencyId",
                            description: "Currency Id",
                            type: "string",
                            example: "USDT.Tron"
                        ),
                        new OA\Property(property: "addressTo",
                            description: "Address To Withdrawal",
                            type: "string",
                            example: "TBQhpoxrutttqgnrgDSvmcsGBb4Ac1oJDc"
                        ),
                        new OA\Property(property: "amount",
                            description: "Amount To Withdrawal",
                            type: "string",
                            example: "1"
                        ),
                    ],
                    type: "object"
                )
            ]
        ),
        tags: ['Withdrawal Wallet'],
        responses: [
            new OA\Response(response: 200, description: "Withdrawal Has Been Sent", content: new OA\JsonContent(
                example: '{"result":[],"errors":[]}'
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
        ]
    )]
    public function withdrawalFromProcessingWallet(WithdrawalWalletWithdrawFromProcessingRequest $request, Authenticatable $user)
    {
        #TODO: Permissions seems to be useless
        if ($user->hasPermissionTo(PermissionsEnum::TransfersFunds->value)) {
            throw new ApiException(__('Transfer disabled '), Response::HTTP_BAD_REQUEST);
        }

        $this->withdrawalWalletService->withdrawalFromProcessingWallet(
            user: $user,
            currencyId: CurrencyId::from($request->input('currencyId')),
            addressTo: $request->input('addressTo'),
            amount: $request->input('amount'),
        );

        return DefaultResponseResource::make([]);
    }

}
