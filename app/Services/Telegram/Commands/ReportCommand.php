<?php

namespace App\Services\Telegram\Commands;


use App\Models\TgUser;
use App\Notifications\ReportMenuNotification;
use App\Notifications\ReportNotification;
use App\Services\Report\ReportService;
use Illuminate\Support\Facades\Notification;
use TelegramBot\Api\Types\Message;

class ReportCommand extends BaseCommand
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function getCommand(): string
    {
        return 'report';
    }

    public function getCallable(Message $message, $command = null): void
    {
        if (is_null($command)) {
            Notification::sendNow($message->getChat(), new ReportMenuNotification());
            return;
        }

        $period = match ($command) {
            'dailyReport' => [now()->yesterday()->toDate(), now()->startOfDay()->toDate()],
            'weeklyReport' => [now()->subWeek()->startOfWeek()->toDate(), now()->subWeek()->endOfWeek()->toDate()],
            'monthlyReport' => [now()->subMonth()->startOfMonth()->toDate(), now()->subMonth()->endOfMonth()->toDate()],
        };

        $tgUser = TgUser::where('chat_id', $message->getChat()->getId())
                ->with('user')
                ->firstOrFail();

        $data =  $this->reportService->statsByUser($period, $tgUser->user);
        Notification::locale($message->getFrom()->getLanguageCode())
                ->sendNow($tgUser, new ReportNotification((object) $data, $period));

    }

}