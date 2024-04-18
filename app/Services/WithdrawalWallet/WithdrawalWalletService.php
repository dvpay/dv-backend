<?php
declare(strict_types=1);

namespace App\Services\WithdrawalWallet;

use App\Dto\Transfer\TransferDto;
use App\Enums\Blockchain;
use App\Enums\CurrencyId;
use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Enums\TransactionType;
use App\Enums\TransferKind;
use App\Enums\TransferStatus;
use App\Models\Currency;
use App\Models\HotWallet;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\WithdrawalWallet;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\BalanceGetter;
use App\Services\Processing\Contracts\ProcessingWalletContract;
use App\Services\Processing\Contracts\TransferContract;
use App\Services\Processing\TransferService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * WithdrawalWalletService
 */
final readonly class WithdrawalWalletService
{
    /**
     *  Current average tron network required energy for a transfer.
     */
    public const TRON_ENERGY_TRANSFER = 64000;

    public const USDT_TRC20_MIN_BALANCE = 0.5;

    /**
     * @param BalanceGetter $balanceGetter
     * @param CurrencyRateService $currencyService
     * @param ProcessingWalletContract $processingWalletService
     * @param TransferService $transferService
     */
    public function __construct(
        private BalanceGetter            $balanceGetter,
        private CurrencyRateService      $currencyService,
        private ProcessingWalletContract $processingWalletService,
        private TransferService          $transferService,
        private TransferContract $transferProcessingContract
    )
    {
    }

    /**
     * @param Authenticatable|User $user
     *
     * @return void
     */
    public function createWallets(Authenticatable|User $user): void
    {
        Currency::where('is_fiat', false)->where('has_balance', true)->each(function ($currency) use ($user) {
            WithdrawalWallet::create([
                'user_id'                => $user->id,
                'exchange_id'            => null,
                'currency'               => Str::lower($currency->code->value),
                'blockchain'             => $currency->blockchain->value,
                'chain'                  => $currency->chain,
                'withdrawal_enabled'     => false,
                'withdrawal_min_balance' => 0,
                'withdrawal_interval'    => null,
            ]);
        });
    }

    /**
     * @param Authenticatable|User $user
     * @param string|null $chain
     *
     * @return void
     */
    public function withdrawal(Authenticatable|User $user, ?string $chain): void
    {
        if ($chain) {
            $withdrawalWallet = $user->withdrawalWallets()
                ->where('chain', $chain)
                ->where('withdrawal_enabled', true)
                ->firstOrFail();
            $this->sendWithdrawal($withdrawalWallet, $user);
        } else {
            $user->withdrawalWallets()
                ->where('withdrawal_enabled', true)
                ->get()
                ->each(fn($wallet) => $this->sendWithdrawal($wallet, $user));
        }
    }

    /**
     * @param Authenticatable|User $user
     * @param string $currencyId
     * @param string $address
     *
     * @return void
     */
    public function withdrawalFromAddress(Authenticatable|User $user, string $currencyId, string $address): void
    {
        $currency = Currency::where([
            ['id', $currencyId],
            ['has_balance', true],
        ])->first();

        $withdrawalWallet = $user->withdrawalWallets()
            ->where('chain', $currency->chain)
            ->where('withdrawal_enabled', true)
            ->firstOrFail();

        $transaction = Transaction::where('type', TransactionType::Transfer->value)
            ->where('from_address', $address)
            ->whereIn('to_address', $withdrawalWallet->address->pluck('address'))
            ->first();

        if (empty($transaction)) {
            $addressForWithdrawal = $withdrawalWallet->address()->inRandomOrder()->first()->address;
        } else {
            $addressForWithdrawal = $transaction->to_address;
        }
        Log::error('Sending a withdrawal request', ['ownerId' => $user->processing_owner_id, 'walletBlockchain' => $withdrawalWallet->blockchain->value, 'address_from' => $address, 'address_to' => $addressForWithdrawal]);

        $addressWallet = HotWallet::where('address', $address)->first();

        $dto = new TransferDto([
            'uuid'        => Str::uuid(),
            'user'        => $user,
            'kind'        => TransferKind::TransferFromAddress,
            'currency'    => $currency,
            'status'      => TransferStatus::Waiting,
            'addressFrom' => $address,
            'addressTo'   => $addressForWithdrawal,
            'contract'    => $currency->contract_address,
            'amount'      => $addressWallet->amount,
            'amountUsd'   => $addressWallet->amount_usd,
        ]);

        $this->transferProcessingContract->transferFromAddress($dto);

        $this->transferService->createTransfer($dto);
    }

    /**
     * @param Authenticatable|User $user
     * @param CurrencyId $currencyId
     * @param string $addressTo
     * @param string $amount
     *
     * @return void
     */

    public function withdrawalFromProcessingWallet(
        Authenticatable|User $user,
        CurrencyId $currencyId,
        string $addressTo,
        string $amount,
    ): void
    {
        $currency = Currency::where([
            ['id', $currencyId->value],
            ['has_balance', true],
        ])->firstOrFail();

        if (!preg_match($currency->blockchain->getAddressValidationRegex(), $addressTo)) {
            throw new UnprocessableEntityHttpException('Destination address is not walid for blockchain ' . $currency->blockchain->value);
        }


        $processingWallets = collect($this->processingWalletService->getWallets($user->processing_owner_id));

        $processingWallet = $processingWallets->firstWhere('blockchain','=',$currencyId->getBlockchain());

        $amountUsd = bcmul($amount, $this->getRate($currency));
#TODO: We can cot check amount while processing do not send us not native balances (Merchant-1295)
//        $processingWalletBalanceUsd = bcmul($processingWallet->balance,$this->getRate($currency));
//
//        if($processingWalletBalanceUsd - $amountUsd < self::USDT_TRC20_MIN_BALANCE) {
//            Log::error('Processing wallet after withdrawal is less, then min balance', [
//                'ownerId'          => $user->processing_owner_id,
//                'walletBlockchain' => $currency->id,
//                'address_from'     => $processingWallet->address,
//                'address_to'       => $addressTo,
//                'amount_usd'       => $amountUsd,
//                'balance_usd'      => $processingWalletBalanceUsd,
//                'min_balance'      => self::USDT_TRC20_MIN_BALANCE,
//            ]);
//        }

        Log::error('Sending a withdrawal from processing request', [
            'ownerId' => $user->processing_owner_id,
            'walletBlockchain' => $currency->id,
            'address_from' => $processingWallet->address,
            'address_to' => $addressTo,
        ]);

        $dto = new TransferDto(
            [
                'uuid'        => Str::uuid(),
                'user'        => $user,
                'kind'        => TransferKind::TransferFromProcessing,
                'currency'    => $currency,
                'status'      => TransferStatus::Waiting,
                'addressFrom' => $processingWallet->address,
                'addressTo'   => $addressTo,
                'contract'    => $currency->contract_address,
                'amount'      => $amount,
                'amountUsd'   => $amountUsd,
            ]
        );

        $this->transferProcessingContract->transferFromProcessing($dto);

        $this->transferService->createTransfer($dto);

    }

    /**
     * @param WithdrawalWallet $withdrawalWallet
     * @param Authenticatable|User $user
     *
     * @return void
     */
    public function sendWithdrawal(WithdrawalWallet $withdrawalWallet, Authenticatable|User $user): void
    {
        $currency = Currency::where([
            ['blockchain', $withdrawalWallet->blockchain->value],
            ['chain', $withdrawalWallet->chain],
            ['has_balance', true],
        ])->first();

        $rate = $this->getRate($currency);
        $addresses = $this->balanceGetter->getAddressBalanceByOwnerId($user->processing_owner_id, $currency->blockchain->value);

        if ($currency->blockchain === Blockchain::Tron) {
            $limit = $this->maxEnergyTransfer($user->processing_owner_id);
            if ($limit <= 0) {
                return;
            }
            $addresses = array_slice($addresses, 0, $limit);
        }

        foreach ($addresses as $address) {
            if ($address['blockchain'] !== $withdrawalWallet->blockchain->value) {
                continue;
            }
            $balance = bcmul($address['balance'], $rate);

            if ($balance < 1) {
                continue;
            }

            $transaction = Transaction::where('type', TransactionType::Transfer->value)
                ->where('from_address', $address['address'])
                ->whereIn('to_address', $withdrawalWallet->address->pluck('address'))
                ->first();

            if (empty($transaction)) {
                $addressForWithdrawal = $withdrawalWallet->address()->inRandomOrder()->first()->address;
            } else {
                $addressForWithdrawal = $transaction->to_address;
            }
            Log::error('Sending a withdrawal request', ['ownerId' => $user->processing_owner_id, 'walletBlockchain' => $withdrawalWallet->blockchain->value, 'address_from' => $address['address'], 'address_to' => $addressForWithdrawal]);

            $dto = new TransferDto([
                'uuid'        => Str::uuid(),
                'user'        => $user,
                'currency'    => $currency,
                'status'      => TransferStatus::Waiting,
                'addressFrom' => $address['address'],
                'addressTo'   => $addressForWithdrawal,
                'contract'    => $currency->contract_address,
                'amount'      => $address["amount"],
                'amountUsd'   => $address["amount_usd"],
            ]);

            $this->transferService->createTransfer($dto);
        }
    }

    /**
     * @param string $ownerId
     *
     * @return int
     */
    public function maxEnergyTransfer(string $ownerId): int
    {
        $walletsResource = collect($this->processingWalletService->getWallets($ownerId))->where('blockchain', Blockchain::Tron->value)->first();
        $transferInWork = $this->transferService->getTransferInWorkCount();
        $energyUsed = $transferInWork * self::TRON_ENERGY_TRANSFER;
        $energyAvailable = (int)$walletsResource->energy - $energyUsed;
        if ($energyAvailable < 0) {
            return 0;
        }
        return intval($energyAvailable / self::TRON_ENERGY_TRANSFER);
    }

    /**
     * @param Currency $currency
     *
     * @return mixed
     */
    private function getRate(Currency $currency)
    {
        $data = $this->currencyService->getCurrencyRate(
            source: RateSource::Binance,
            from: CurrencySymbol::USD,
            to: $currency->code,
        );
        return $data['rate'];
    }

    /**
     * @param WithdrawalWallet $withdrawalWallet
     *
     * @return TransferDto|null
     */
    public function loopWithdrawal(WithdrawalWallet $withdrawalWallet): TransferDto|null
    {
        $currency = Currency::where([
            ['blockchain', $withdrawalWallet->blockchain->value],
            ['chain', $withdrawalWallet->chain],
            ['has_balance', true],
        ])->first();

        $activeTransfer = Transfer::where('status', TransferStatus::Sending)
            ->where('user_id', $withdrawalWallet->user_id)
            ->where('kind', TransferKind::TransferFromAddress)
            ->where('currency_id', $currency->id)
            ->get()
            ->pluck('address_from')
            ->toArray();

        $failedTransfer = Transfer::where('status', TransferStatus::Failed)
            ->where('user_id', $withdrawalWallet->user_id)
            ->where('kind', TransferKind::TransferFromAddress)
            ->where('currency_id', $currency->id)
            ->where('created_at', '>', now()->subMinutes(60))
            ->whereNotNull('message')
            ->get()
            ->pluck('address_from')
            ->toArray();

        $address = HotWallet::where('user_id', $withdrawalWallet->user_id)
            ->where('blockchain', $withdrawalWallet->blockchain)
            ->where('amount_usd', '>=', $withdrawalWallet->withdrawal_min_balance)
            ->where('amount', '>=', $currency->withdrawal_min_balance)
            ->whereNotIn('address', $activeTransfer)
            ->whereNotIn('address', $failedTransfer)
            ->orderBy('amount_usd', 'desc')
            ->first();

        if (empty($address)) {
            return null;
        }

        /* hack for stop transfer if processing return error callback  */
        $failedTransfer = Transfer::where('status', TransferStatus::Failed)
            ->where('user_id', $withdrawalWallet->user_id)
            ->where('kind', TransferKind::TransferFromAddress)
            ->where('address_from', $address->address)
            ->where('currency_id', $currency->id)
            ->where('created_at', '>=', now()->subMinutes(60))
            ->first();

        if (!empty($failedTransfer)) {
            return null;
        }

        $transaction = Transaction::where('type', TransactionType::Transfer->value)
            ->where('from_address', $address->address)
            ->whereIn('to_address', $withdrawalWallet->address->pluck('address'))
            ->first();

        if (empty($transaction)) {
            $addressForWithdrawal = $withdrawalWallet->address()->inRandomOrder()->first()->address;
        } else {
            $addressForWithdrawal = $transaction->to_address;
        }

        return new TransferDto([
            'uuid'        => Str::uuid(),
            'user'        => $withdrawalWallet->user,
            'kind'        => TransferKind::TransferFromAddress,
            'currency'    => $currency,
            'status'      => TransferStatus::Sending,
            'addressFrom' => $address['address'],
            'addressTo'   => $addressForWithdrawal,
            'contract'    => $currency->contract_address,
            'amount'      => $address->amount,
            'amountUsd'   => $address->amount_usd,
        ]);
    }
}
