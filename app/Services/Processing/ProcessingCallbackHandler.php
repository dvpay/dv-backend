<?php

declare(strict_types=1);

namespace App\Services\Processing;

use App\Dto\ProcessingCallbackDto;
use App\Enums\ProcessingCallbackType;
use App\Services\Processing\Contracts\CallbackHandlerContract;

readonly class ProcessingCallbackHandler
{
    public function __construct(
        private CallbackHandlerContract $watchHandler,
        private CallbackHandlerContract $transferHandler,
        private CallbackHandlerContract $paymentHandler,
        private CallbackHandlerContract $transferStatusHandler,
    )
    {
    }

    public function handle(ProcessingCallbackDto $dto): void
    {
        switch ($dto->type) {
            case ProcessingCallbackType::Expired:
            case ProcessingCallbackType::Watch:
                $this->watchHandler->handle($dto);
                break;
            case ProcessingCallbackType::Transfer:
                $this->transferHandler->handle($dto);
                break;
            case ProcessingCallbackType::Deposit:
                $this->paymentHandler->handle($dto);
                break;
            case ProcessingCallbackType::TransferJob:
                $this->transferStatusHandler->handle($dto);
        }
    }
}
