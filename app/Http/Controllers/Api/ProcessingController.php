<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\ProcessingCallbackDto;
use App\Enums\Blockchain;
use App\Enums\InvoiceStatus;
use App\Enums\ProcessingCallbackType;
use App\Enums\TransferStatus;
use App\Http\Requests\Processing\ProcessingCallbackRequest;
use App\Http\Requests\Processing\ProcessingTransferTypeRequest;
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
use OpenApi\Attributes as OA;

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
    #[OA\Get(
        path: '/stores/processing/wallets',
        summary: 'Get processing wallets',
        security: [["bearerAuth" => []]],
        tags: ['Processing'],
        responses: [
            new OA\Response(response: 200, description: "Get static address", content: new OA\JsonContent(
                example: '{"result":[{"blockchain":"tron","address":"nQl6jcneD49hgLOQiBliKQ1ZlgelMnSRyhgtTMHJ","balance":"0.5","minBalance":"10.00000000","energyLimit":"4741639","energy":"78347","bandwidthLimit":"2504313","bandwidth":"7191768","transferType":"delegate","transferTypeList":["delegate","regular"]},{"blockchain":"bitcoin","address":"6XwBAkzyISK2vwpbiLmAeQ3F527uwUmrvLerGyZV","balance":"0.5","minBalance":"0.00030000","energyLimit":"2151293","energy":"9314641","bandwidthLimit":"8420492","bandwidth":"9072419","transferType":"delegate","transferTypeList":["delegate","regular"]},{"blockchain":"ethereum","address":"s1LxynL6xVOKjFkEDSVLEHyuV5vHb1qgz5a4XLwI","balance":"0.5","minBalance":"10.00000000","energyLimit":"4857592","energy":"3095146","bandwidthLimit":"7094485","bandwidth":"436986","transferType":"delegate","transferTypeList":["delegate","regular"]}],"errors":[]}',
            )),
        ],

    )]
    public function getProcessingWallets(Request $request): ProcessingWalletCollection
    {
        $user = $request->user();

        $result = $this->processingWalletContract->getWallets($user->processing_owner_id);

        return new ProcessingWalletCollection($result);
    }

    public function updateProcessingTransferType(ProcessingTransferTypeRequest $request, Authenticatable $user)
    {
        $result = $this->processingWalletContract->switchType(
            ownerId: $user->processing_owner_id,
            blockchain: $request->input('blockchain'),
            type: $request->input('type')
        );

        return new DefaultResponseResource([]);
    }

    /**
     * @throws \Throwable
     */
    public function transferCallback(TransferRequest $request, TransferService $transferService)
    {
        ProcessingCallback::create(['request' => json_encode($request->all())]);
        $transferService->updateStatus(
            $request->input('uuid'),
            TransferStatus::tryFrom($request->input('status')),
            $request->input('error')
        );

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
