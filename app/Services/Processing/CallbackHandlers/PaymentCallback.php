<?php

namespace App\Services\Processing\CallbackHandlers;

use App\Dto\ProcessingCallbackDto;
use App\Jobs\PaymentCallbackJob;
use App\Models\PayerAddress;
use App\Services\Processing\Contracts\CallbackHandlerContract;

class PaymentCallback implements CallbackHandlerContract
{

    public function handle(ProcessingCallbackDto $dto)
    {
        $payerAddress = PayerAddress::where([
                ['blockchain', $dto->blockchain],
                ['payer_id', $dto->payer_id],
                ['address', $dto->address]
        ])->firstOrFail();

        PaymentCallbackJob::dispatch($dto, $payerAddress);
    }
}