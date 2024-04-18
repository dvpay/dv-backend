<?php

namespace Tests\Unit\Settings;


 use App\Enums\RootSetting;
 use App\Facades\Settings;
 use App\Services\Processing\Contracts\OwnerContract;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Tests\TestCase;

class SettingSetTest extends TestCase
{
    use RefreshDatabase;

    public function testItUpdatesCallbackUrlAtProcessing()
    {
        $url = 'http://example.com/';
        $expectedUrl = trim($url,'/') . route(name: 'processing.callback', absolute: false);

        $clientID = 'TODD';

        \Config::set('processing.client.id', $clientID);

        $processingService = \Mockery::mock(OwnerContract::class);

        $processingService->shouldReceive('updateCallbackUrl')
            ->withArgs([$clientID, $expectedUrl])
            ->once()
            ->ordered()
            ->andReturn(true);

        $this->app->instance(OwnerContract::class, $processingService);

        Settings::set(RootSetting::AppUrl->value, $url);

        $this->assertDatabaseHas('settings', [
            'name' => RootSetting::AppUrl->value,
            'value' => $url,
        ]);

    }

 }
