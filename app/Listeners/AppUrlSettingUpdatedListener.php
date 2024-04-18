<?php

namespace App\Listeners;

use App\Enums\RootSetting;
use App\Events\SettingUpdatedEvent;
use App\Facades\Settings;
use App\Services\Processing\Contracts\OwnerContract;

class AppUrlSettingUpdatedListener
{
    public function __construct(protected OwnerContract $ownerContract)
    {
    }

    public function handle(SettingUpdatedEvent $event): void
    {
        if(RootSetting::AppUrl->value === $event->setting->name) {
            $clientID = config('processing.client.id');
            $url =  route(name: 'processing.callback', absolute: false);
            $url = trim($event->setting->value,'/') . $url;

            $this->ownerContract->updateCallbackUrl(clientID: $clientID, url: $url);
        }
    }
}
