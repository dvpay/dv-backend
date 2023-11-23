<?php

namespace App\Console\Commands;

use App\Models\TgUser;
use App\Notifications\ReportNotification;
use App\Services\Report\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class ReportCommand extends Command
{
    protected $signature = 'report {period}';

    protected $description = 'Command send report by merchant';

    public function handle(ReportService $reportService): void
    {
        $period = match ($this->argument('period')) {
            'dailyReport' => [now()->yesterday()->toDate(), now()->startOfDay()->toDate()],
            'weeklyReport' => [now()->subWeek()->startOfWeek()->toDate(), now()->subWeek()->endOfWeek()->toDate()],
            'monthlyReport' => [now()->subMonth()->startOfMonth()->toDate(), now()->subMonth()->endOfMonth()->toDate()],
        };

        TgUser::with('user')
                ->get()
                ->each(function ($tgUser) use ($reportService, $period) {
                    if ($tgUser->user->notifications->contains('slug', $this->argument('period'))) {
                        $data = $reportService->statsByUser($period, $tgUser?->user);
                        Notification::send($tgUser, new ReportNotification((object) $data, $period));
                    }
                });
    }
}
