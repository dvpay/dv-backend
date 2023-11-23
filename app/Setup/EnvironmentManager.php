<?php
declare(strict_types=1);

namespace App\Setup;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 *
 */
class EnvironmentManager
{
    /**
     * @return bool
     */
    public function copyEnv(): bool
    {
        if (!file_exists($this->getEnvPath())) {
            return copy(base_path('.env.example'), $this->getEnvPath());
        }
        return true;
    }

    /**
     * @param Request $request
     * @return false|int|void
     */
    public function saveFileWizard(Request $request)
    {
        $envFileData =
            /*App*/
            'APP_NAME=\'' . $request->input('app_name', config('app.name')) . "'\n" .
            'APP_ENV=' . $request->input('environment', 'production') . "\n" .
            'APP_KEY=' . 'base64:' . base64_encode(Str::random(32)) . "\n" .
            'APP_DEBUG=' . $request->input('app_debug', 'false') . "\n" .
            'APP_URL=' . $request->input('app_url', $request->root()) . "\n\n" .
            'APP_DOMAIN=' . $request->input('app_domain', $request->root()) . "\n\n" .
            /*Database*/
            'DB_CONNECTION=' . $request->input('database_connection') . "\n" .
            'DB_HOST=' . $request->input('database_hostname') . "\n" .
            'DB_PORT=' . $request->input('database_port') . "\n" .
            'DB_DATABASE=' . $request->input('database_name') . "\n" .
            'DB_USERNAME=' . $request->input('database_username') . "\n" .
            'DB_PASSWORD=' . $request->input('database_password') . "\n\n" .

            'BROADCAST_DRIVER=' . 'log' . "\n" .
            'CACHE_DRIVER=' . 'file' . "\n" .
            'FILESYSTEM_DRIVER=' . 'local' . "\n" .
            'QUEUE_CONNECTION=' . 'redis' . "\n" .
            'SESSION_DRIVER=' . 'file' . "\n" .
            'SESSION_LIFETIME=' . '120' . "\n\n" .
            /* Redis */
            'REDIS_HOST=' . $request->input('redis_host') . "\n" .
            'REDIS_PASSWORD=' . $request->input('redis_password') . "\n" .
            'REDIS_PORT=' . $request->input('redis_port') . "\n\n" .
            /* Processing */
            'PROCESSING_URL=' . $request->input('processing_host') . "\n" .
            'PROCESSING_CLIENT_ID=' . "\n" .
            'PROCESSING_CLIENT_KEY=' . "\n" .
            'PROCESSING_WEBHOOK_KEY=' . "\n\n" .
            /* Something default value*/
            'WEBHOOK_TIMEOUT=50' . "\n" .
            'MIN_TRANSACTION_CONFIRMATIONS=1' . "\n" .
            'RATE_SCALE=1' . "\n\n" .
            'FINISH_INSTALL=' . false . "\n";

        if ($this->copyEnv()) {
            return file_put_contents($this->getEnvPath(), $envFileData);
        }
    }

    /**
     * @return string
     */
    public function getEnvPath(): string
    {
        return base_path('.env');
    }

    /**
     * @return string
     */
    public function getFrontendEnvPath(): string
    {
        return base_path('/frontend/.env');
    }

    /**
     * @return bool
     */
    public function copyEnvFrontend(): bool
    {
        if(!file_exists($this->getFrontendEnvPath())) {
            return copy(base_path('/frontend/.env.example'), $this->getFrontendEnvPath());
        }

        return true;
    }

    /**
     * @param string $apiUrl
     * @return false|int|void
     */
    public function saveEnvFrontend(string $apiUrl)
    {
        $envFileData =
            'VITE_API_URL=' . $apiUrl . "\n".
            'VITE_INVOICE_POLLING_TIMEOUT=10';

        if ($this->copyEnvFrontend()) {
            return file_put_contents($this->getFrontendEnvPath(), $envFileData);
        }
    }
}
