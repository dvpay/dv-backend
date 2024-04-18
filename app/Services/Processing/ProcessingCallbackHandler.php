<?php

declare(strict_types=1);

namespace App\Services\Processing;

use App\Dto\ProcessingCallbackDto;
use App\Enums\Metric;
use App\Enums\ProcessingCallbackType;
use App\Facades\Prometheus;
use App\Services\Processing\Contracts\CallbackHandlerContract;

readonly class ProcessingCallbackHandler
{
    public function __construct(
        private CallbackHandlerContract $transferHandler,
        private CallbackHandlerContract $paymentHandler,
    )
    {
    }

    public function handle(ProcessingCallbackDto $dto): void
    {
        Prometheus::counterInc(
            name: Metric::ProcessingCallbackReceived->getName(),
            labels: [$dto->type->value]
        );

        switch ($dto->type) {
            case ProcessingCallbackType::Transfer:
                $this->transferHandler->handle($dto);
                break;
            case ProcessingCallbackType::Deposit:
                $this->paymentHandler->handle($dto);
                break;
        }
    }
}
