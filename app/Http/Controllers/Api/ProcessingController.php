<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\ProcessingCallbackDto;
use App\Enums\Blockchain;
use App\Enums\InvoiceStatus;
use App\Enums\ProcessingCallbackType;
use App\Enums\TransferStatus;
use App\Http\Requests\Processing\ProcessingCallbackRequest;
use App\Http\Requests\Processing\TransferRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Processing\ProcessingWalletCollection;
use App\Http\Resources\Processing\ProcessingWalletStatsCollection;
use App\Http\Resources\Processing\ProcessingWalletTransferCollection;
use App\Models\Currency;
use App\Models\ProcessingCallback;
use App\Services\Processing\Contracts\ProcessingWalletContract;
use App\Services\Processing\ProcessingCallbackHandler;
use App\Services\Processing\TransferService;
use App\Services\Report\ReportService;
use DateTime;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ProcessingController
 */
class ProcessingController extends ApiController
{
    /**
     * @param ProcessingCallbackHandler $callbackHandler
     * @param ProcessingWalletContract $processingWalletContract
     */
    public function __construct(
        private readonly ProcessingCallbackHandler $callbackHandler,
        private readonly ProcessingWalletContract  $processingWalletContract,
        private readonly ReportService             $reportService
    )
    {
    }

    /**
     * @param ProcessingCallbackRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function callback(ProcessingCallbackRequest $request): JsonResponse
    {
        $input = $request->input();
        $input['transferStatus'] = TransferStatus::tryFrom($input['status'] ?? '');
        $input['status'] = InvoiceStatus::tryFrom($input['status'] ?? '');
        $input['blockchain'] = Blockchain::tryFrom($input['blockchain']);
        $input['type'] = ProcessingCallbackType::tryFrom($input['type']);

        ProcessingCallback::create(['request' => json_encode($request->all())]);

        $dto = new ProcessingCallbackDto($input);

        $this->callbackHandler->handle($dto);

        return (new DefaultResponseResource([]))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * @param Request $request
     * @return ProcessingWalletCollection
     */
    public function getProcessingWallets(Request $request): ProcessingWalletCollection
    {
        $user = $request->user();

        $result = $this->processingWalletContract->getWallets($user->processing_owner_id);

        return new ProcessingWalletCollection($result);
    }

    /**
     * @throws \Throwable
     */
    public function transferCallback(TransferRequest $request, TransferService $transferService)
    {
        ProcessingCallback::create(['request' => json_encode($request->all())]);
        $transferService->updateStatus($request->input('uuid'), TransferStatus::tryFrom($request->input('status')));

        return (new DefaultResponseResource([]))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    /**
     * @param Blockchain $blockchain
     * @param Authenticatable $user
     * @return ProcessingWalletStatsCollection
     */
    public function getProcessingWalletInfo(Blockchain $blockchain, Authenticatable $user): ProcessingWalletStatsCollection
    {
        $currency = Currency::where('blockchain', $blockchain)
            ->where('has_balance', true)
            ->first();

        $stats = $this->reportService->transferStatsByUserAndCurrency($currency, $user);
        return ProcessingWalletStatsCollection::make($stats);
    }

    /**
     * @param Blockchain $blockchain
     * @param Authenticatable $user
     * @return DefaultResponseResource
     */
    public function getProcessingWalletStats(Blockchain $blockchain, Authenticatable $user): DefaultResponseResource
    {
        $currency = Currency::where('blockchain', $blockchain)
            ->where('has_balance', true)
            ->first();

        $stats = $this->reportService->getTransferStatByDate($currency, $user);
        return DefaultResponseResource::make($stats);
    }


    /**
     * @param Blockchain $blockchain
     * @param Authenticatable $user
     * @param Request $request
     * @return ProcessingWalletTransferCollection
     * @throws \Exception
     */
    public function getProcessingWalletTransfers(Blockchain $blockchain, Authenticatable $user, Request $request): ProcessingWalletTransferCollection
    {
        $currency = Currency::where('blockchain', $blockchain)
            ->where('has_balance', true)
            ->first();

        $date = new DateTime($request->input('date'));
        $stats = $this->reportService->getTransferByDate($currency, $user, $date);
        return ProcessingWalletTransferCollection::make($stats);
    }

}
