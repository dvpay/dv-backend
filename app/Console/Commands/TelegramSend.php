<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\TelegramNotificationJob;
use Illuminate\Console\Command;

class TelegramSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "telegram:send {message=Ola  🎉🎉🎉}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send message to Telegram bot.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $time = time();

        $message = $this->argument('message');

        TelegramNotificationJob::dispatchSync($message);

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}