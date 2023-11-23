<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;
use TelegramBot\Api\Exception;

class TelegramWebhookSet extends Command
{
    public function __construct(
        private readonly TelegramService $telegramService,
        private readonly string $defaultTelegramWebhookUrl
    )
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "telegram:webhook:set {url?}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set Telegram webhook url.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $time = time();

        $url = $this->argument('url') ?? $this->defaultTelegramWebhookUrl;

        $this->telegramService->setWebhookUrl($url);

        $info = $this->telegramService->getWebhookInfo();

        $this->telegramService->setCommands();

        $this->info($info->getUrl());

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}