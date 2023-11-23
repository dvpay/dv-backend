<?php

namespace App\Jobs;

use App\Notifications\ExceptionNotification;
use App\Services\Telegram\TelegramService;
use App\Services\User\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;

/**
 * TelegramNotificationJob
 */
class TelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** todo remove $users after refactoring all notifications
     * @param string $message
     * @param array $users
     */
    public function __construct(
        private readonly string $message,
        private readonly array $users = []
    )
    {
    }

    /**
     * @param TelegramService $tgService
     * @param UserService $userService
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function handle(UserService $userService): void
    {
        $users = $userService->getAllRoot();

        Notification::send($users, new ExceptionNotification($this->message));
    }
}
