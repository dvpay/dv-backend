<?php

declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Dto\ProcessingCallbackDto;

interface CallbackHandlerContract
{
    public function handle(ProcessingCallbackDto $dto);
}
