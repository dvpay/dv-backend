<?php

namespace App\Console\Commands;

use App\Services\Processing\Contracts\OwnerContract;
use Illuminate\Console\Command;

class UpdateProcessingCallbackUrlCommand extends Command
{
    protected $signature = 'update:processing:callback:url';

    protected $description = 'Command for update processing url';

    public function handle(OwnerContract $ownerContract): void
    {
        $clientID = config('processing.client.id');
        $url = route('processing.callback');
        $ownerContract->updateCallbackUrl(clientID: $clientID, url: $url);

        $this->info('Callback url success update');
    }
}
