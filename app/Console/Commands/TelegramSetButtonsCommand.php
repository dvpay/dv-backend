<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class TelegramSetButtonsCommand extends Command
{
    protected $signature = 'telegram:set:buttons';

    protected $description = 'Command description';

    public function handle(TelegramService $telegramService): void
    {
        $telegramService->setCommands();
    }
}
