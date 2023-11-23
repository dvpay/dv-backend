<?php

namespace App\Services\Processing\CallbackHandlers;

use App\Dto\ProcessingCallbackDto;
use App\Services\Processing\Contracts\CallbackHandlerContract;
use App\Services\Processing\TransferService;
use Throwable;

readonly class TransferStatusCallback implements CallbackHandlerContract
{

    public function __construct(
        private TransferService $transferService
    )
    {
    }

    /**
     * @throws \Throwable
     */
    public function handle(ProcessingCallbackDto $dto)
    {
        $this->transferService->updateStatus($dto->uuid, $dto->transferStatus);
    }
}
