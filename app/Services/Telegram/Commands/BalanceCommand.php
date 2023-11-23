<?php
declare(strict_types=1);

namespace App\Services\Telegram\Commands;

use App\Models\TgUser;
use App\Notifications\BalanceNotification;
use App\Services\Report\ReportService;
use Illuminate\Support\Facades\Notification;
use Psr\SimpleCache\InvalidArgumentException;
use TelegramBot\Api\Types\Message;

/**
 *
 */
class BalanceCommand extends BaseCommand
{
    /**
     * @param  ReportService  $reportService
     */
    public function __construct(private readonly ReportService $reportService)
    {
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return 'balance';
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCallable(Message $message, $command = null): void
    {
        $tgUser = TgUser::where('chat_id', $message->getChat()->getId())
                ->with('user')
                ->firstOrFail();
        
        $data = $this->reportService->balanceByUser($tgUser->user);
        Notification::locale($message->getFrom()->getLanguageCode())
                ->sendNow($tgUser, new BalanceNotification((object) $data));
    }
}