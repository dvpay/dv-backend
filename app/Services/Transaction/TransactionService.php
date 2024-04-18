<?php

declare(strict_types=1);

namespace App\Services\Transaction;

use App\Dto\GetTransactionInfoDto;
use App\Dto\ProcessingCallbackDto;
use App\Dto\ProcessingTransactionInfoDto;
use App\Enums\Blockchain;
use App\Enums\InvoiceStatus;
use App\Enums\ProcessingCallbackType;
use App\Enums\RateSource;
use App\Enums\TransactionType;
use App\Exceptions\ApiException;
use App\Exceptions\RateNotFoundException;
use App\Jobs\PaymentCallbackJob;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use App\Models\Payer;
use App\Models\PayerAddress;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\CurrencyRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\WebhookSendHistoryRepository;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\Contracts\TransactionContract;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 *   todo write interface
 */
readonly class TransactionService
{

    /**
     * @param TransactionRepository $transactionRepository
     * @param InvoiceRepository $invoiceRepository
     * @param CurrencyRepository $currencyRepository
     * @param WebhookSendHistoryRepository $webhookSendHistoryRepository
     * @param TransactionContract $transactionContract
     * @param Repository $cache
     * @param CurrencyRateService $currencyRateService
     */
    public function __construct(
        private TransactionRepository        $transactionRepository,
        private InvoiceRepository            $invoiceRepository,
        private CurrencyRepository           $currencyRepository,
        private WebhookSendHistoryRepository $webhookSendHistoryRepository,
        private TransactionContract          $transactionContract,
        private Repository                   $cache,
        private CurrencyRateService          $currencyRateService,

    )
    {
    }

    /**
     * @param string $txId
     * @param User $user
     * @param int|null $subDays
     * @return GetTransactionInfoDto
     * @throws InvalidArgumentException
     */
    public function getTransactionInfo(string $txId, User $user, int|null $subDays = null): GetTransactionInfoDto
    {
        if ($transaction = $this->transactionRepository->getByTxId($txId)) {
            if (!$user->allStores()->pluck('id')->contains($transaction->store_id)) {
                throw new NotFoundHttpException('Transaction store does not belong to user.');
            }
            $result['transaction'] = $transaction;
            $result['currency'] = $this->currencyRepository->getById($transaction->currency_id);
            $result['invoice'] = $this->invoiceRepository->getById($transaction->invoice_id);
            $result['webhooks'] = $this->webhookSendHistoryRepository->getByInvoiceId($result['invoice']->id);
        } else {
            $result['transaction'] = $this->transactionContract->info($txId);
            $this->cache->set($txId, $result['transaction']);

            if ($result['transaction']->payerId) {
                $result['payer'] = $this->getPayer($result['transaction']->payerId);
            }
            if (!$user->allStores()->pluck('id')->contains($result['payer']?->store_id)) {
                throw new NotFoundHttpException('Transaction store does not belong to user.');
            }

            $result['currency'] = Currency::where([
                ['blockchain', $result['transaction']->blockchain],
                ['contract_address', $result['transaction']->contractAddress],
            ])->first();


        }

        return new GetTransactionInfoDto($result);
    }


    /**
     * @param string $txId
     * @param Invoice $invoice
     * @param User|Authenticatable $user
     * @return void
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function forceAttachTransactionToInvoice(string $txId, Invoice $invoice, User|Authenticatable $user): void
    {

        try {
            DB::beginTransaction();

            if (!$transactionDTO = $this->getTransactionInfo($txId, $user)) {
                throw new NotFoundHttpException(__('Transaction not found.'));
            }

            if (!$user->allStores()->contains('id', $invoice->store->id)) {
                throw new ApiException(__("You don't have permission to this action!"), 403);
            }

            if (isset($transactionDTO->invoice)) {
                throw new ApiException(__('Transaction attached any invoice'), 422);
            }

            if ($transactionDTO->transaction->confirmations < config('processing.min_transaction_confirmations')) {
                throw  new ApiException(__('Not enough transaction confirmations'), 400);
            }

            if ($this->createTransaction($invoice, $transactionDTO->transaction, $transactionDTO->currency)) {
                $invoice->attached_by = $user->id;
                $invoice->attached_at = date('Y-m-d H:i:s');
                $invoice->saveOrFail();
                $invoice->updateStatus(InvoiceStatus::Paid);
            }

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    /**
     * @param Invoice $invoice
     * @param ProcessingTransactionInfoDto $dto
     * @return bool
     * @throws Throwable
     */
    private function createTransaction(Invoice $invoice, ProcessingTransactionInfoDto $dto, Currency $currency): bool
    {

        $transactionExists = Transaction::where('tx_id', $dto->txId)
            ->exists();

        if ($transactionExists) {
            return true;
        }
        $store = $invoice->store;
        $rate = $this->rateCalculation($invoice->store, $currency);

        $transaction = new Transaction([
            'store_id'           => $store->id,
            'user_id'            => $store->user_id,
            'invoice_id'         => $invoice->id,
            'currency_id'        => $currency->id,
            'tx_id'              => $dto->txId,
            'type'               => TransactionType::Invoice,
            'from_address'       => $dto->sender ?? '',
            'to_address'         => $dto->receiver,
            'amount'             => $dto->amount,
            'amount_usd'         => $dto->amount / $rate,
            'rate'               => $rate,
            'fee'                => 0,
            'network_created_at' => $dto->time ? date('Y-m-d H:i:s', strtotime($dto->time)) : null,
        ]);

        return $transaction->saveOrFail();
    }

    /**
     * @param string $txId
     * @param User|Authenticatable $user
     * @return void
     * @throws InvalidArgumentException
     */
    public function attachTransactionToPayer(string $txId, User|Authenticatable $user): void
    {
        if (!$transaction = $this->cache->get($txId)) {
            $this->getTransactionInfo($txId, $user);
        }

        if (!$transaction && !$transaction = $this->cache->get($txId)) {
            throw new NotFoundHttpException(__('Transaction not found.'));
        }
        $transaction = (array)$transaction;

        $transaction['status'] = InvoiceStatus::Paid;
        $transaction['type'] = ProcessingCallbackType::Deposit;
        $transaction['address'] = $transaction['receiver'];
        $transaction['payer_id'] = $transaction['payerId'];
        $transaction['tx'] = $transaction['txId'];

        $dto = new ProcessingCallbackDto($transaction);

        $payerAddress = PayerAddress::where([
            ['blockchain', $dto->blockchain->value],
            ['payer_id', $dto->payer_id],
            ['address', $dto->address]
        ])->firstOrFail();

        PaymentCallbackJob::dispatch($dto, $payerAddress);
    }

    /**
     * @param Store $store
     * @param Currency $currency
     * @return mixed|string
     */
    private function rateCalculation(Store $store, Currency $currency): mixed
    {
        $rateSource = RateSource::fromStore($store);

        $data = $this->currencyRateService->getCurrencyRate(
            $rateSource,
            $store->currency->code,
            $currency->code,
        );

        if (!$data) {
            throw new RateNotFoundException();
        }

        if ($currency->blockchain == Blockchain::Bitcoin) {
            $scale = bcmul($data['rate'], bcdiv($store->rate_scale, '100'));
            $data['rate'] = bcadd($data['rate'], $scale);
        }

        return $data['rate'];

    }


    public function getPayer(string $payerId): Payer|null
    {
        return Payer::where('id', $payerId)->first();
    }
}
