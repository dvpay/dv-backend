<?php

declare(strict_types=1);

namespace App\Services\Processing\CallbackHandlers;

use App\Dto\ProcessingCallbackDto;
use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Enums\TransactionType;
use App\Exceptions\ApiException;
use App\Exceptions\CallbackException;
use App\Models\Currency;
use App\Models\PayerAddress;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Models\User;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\Contracts\CallbackHandlerContract;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class TransferCallback implements CallbackHandlerContract
{
    public function __construct(
        private Connection          $db,
        private CurrencyRateService $currencyService,
        private CurrencyConversion  $currencyConversion,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(ProcessingCallbackDto $dto): void
    {
        try {
            PayerAddress::where([
                ['blockchain', $dto->blockchain],
                ['address', $dto->sender]
            ])->firstOrFail();

            $this->db->beginTransaction();

            if ((float)$dto->amount < 0) {
                throw new CallbackException(__('Negative amount.'), Response::HTTP_BAD_REQUEST);
            }

            $transactionExists = Transaction::where('tx_id', $dto->tx)
                ->where('from_address', $dto->sender)
                ->where('to_address', $dto->address)
                ->where('amount', $dto->amount)
                ->exists();

            if ($transactionExists) {
                $this->db->rollBack();
                return;
            }

            $contractAddress = $dto->contractAddress ?? '';

            $currency = Currency::where([
                ['contract_address', $contractAddress],
                ['blockchain', $dto->blockchain],
            ])->first();

            $user = User::where('processing_owner_id', $dto->ownerId)->first();

            if ($this->checkConfirmation($dto)) {
                $this->createUnconfirmedTransaction($dto, $user, $currency);
                $this->db->commit();
                throw new ApiException(__('Low Confirmations'), 422);
            }
            $this->createTransaction($dto, $user, $currency);

            $this->db->commit();

        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    private function createTransaction(ProcessingCallbackDto $dto, User $user, Currency $currency): void
    {
        $rateSource = RateSource::Binance;
        $from = $currency->code;
        $to = CurrencySymbol::USDT;

        $data = $this->currencyService->getCurrencyRate($rateSource, $from, $to);
        $amountUsd = $this->currencyConversion->convert($dto->amount, $data['rate'], true);

        Transaction::create([
            'user_id'              => $user->id,
            'currency_id'          => $currency->id,
            'tx_id'                => $dto->tx,
            'type'                 => TransactionType::Transfer,
            'from_address'         => $dto->sender ?? '',
            'to_address'           => $dto->address,
            'amount'               => $dto->amount,
            'amount_usd'           => $amountUsd,
            'rate'                 => $data['rate'],
            'fee'                  => 0,
            'withdrawal_is_manual' => $dto->isManual ?? false,
            'network_created_at'   => $dto->time ?? null,
            'energy'               => $dto->energy,
            'bandwidth'            => $dto->bandwidth
        ]);
    }

    private function checkConfirmation(ProcessingCallbackDto $dto): bool
    {
        if ($dto->confirmations < config('processing.min_transaction_confirmations')) {
            return true;
        }
        return false;
    }

    private function createUnconfirmedTransaction(ProcessingCallbackDto $dto, User $user, Currency $currency): void
    {
        UnconfirmedTransaction::firstOrCreate([
            'currency_id' => $currency->id,
            'tx_id'       => $dto->tx,
        ], [
            'user_id'      => $user->id,
            'store_id'     => null,
            'invoice_id'   => null,
            'from_address' => $dto->sender ?? '',
            'to_address'   => $dto->address,
            'tx_id'        => $dto->tx,
            'currency_id'  => $currency->id
        ]);
    }

}

