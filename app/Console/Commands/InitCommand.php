<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Throwable;

class InitCommand extends Command
{
    protected $signature = 'processing:init';

    protected $description = 'Register new user';

    public function handle(): void
    {
        try {
            $response = Http::post(config('processing.url') . '/clients', [
                'callbackUrl' => route('processing.callback'),
            ])
                ->throw()
                ->object();

            $this->putPermanentEnv('PROCESSING_CLIENT_ID', $response?->cid);
            $this->putPermanentEnv('PROCESSING_CLIENT_KEY', $response?->key);
            $this->putPermanentEnv('PROCESSING_WEBHOOK_KEY', $response?->webhookKey);

            Artisan::call('cache:clear');

        } catch (RequestException $e) {
            $response = $e->response->json();
            $errorMessage = $response['error'];

            $this->error('[Error] Response from processing: ' . $errorMessage);
        } catch (Throwable $e) {
            $this->error('[Error] Response from processing: ' . $e->getMessage());
        }
    }

    private function putPermanentEnv($key, $value)
    {
        $path = app()->environmentFilePath();

        $escaped = preg_quote('=' . env($key), '/');

        file_put_contents($path, preg_replace(
            "/^{$key}{$escaped}/m",
            "{$key}={$value}",
            file_get_contents($path)
        ));
    }

}
