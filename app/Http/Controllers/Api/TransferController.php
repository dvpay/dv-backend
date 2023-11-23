<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\TransferStatus;
use App\Http\Controllers\Controller;
use App\Models\HotWallet;
use App\Models\Transfer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

class TransferController extends Controller
{
    public function __invoke(Authenticatable $user)
    {
        return response()->stream(function () use ($user) {
            $timeout = time() + 30;

            while (true) {
                echo "event: ping\n";

                $transfersInProgress = Transfer::where('status', TransferStatus::Sending)
                    ->where('user_id', $user->id)
                    ->limit(14)
                    ->latest()
                    ->get();

                $hotWalletsQuery = HotWallet::whereNotIn('address', $transfersInProgress->pluck('address_from')->toArray())
                    ->where('user_id', $user->id)
                    ->where('amount_usd', '>', 10)
                    ->with('latestTransaction')
                    ->orderBy('amount_usd', 'desc')
                    ->limit(10);

                $hotWallets = $hotWalletsQuery->get()
                    ->map(function ($item) {
                        $item->retry = Cache::get('retryTransfer' . $item->address) ?? 0;
                        return $item;
                    });

                $sumHotWallet = $hotWalletsQuery->sum('amount_usd');
                $countHotWallet = $hotWalletsQuery->count();

                $transferComplete = Transfer::where('status', TransferStatus::Complete)
                    ->where('user_id', $user->id)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->limit(20)
                    ->get();


                if ($transferComplete->count() >= 2) {
                    $firstTransfer = $transferComplete[0];
                    $secondTransfer = $transferComplete[1];
                    $difference = $firstTransfer->updated_at->diff($secondTransfer->updated_at);
                    $averageTransferTime = $difference->i;
                }

                $transferStatsComplete = Transfer::where('status', TransferStatus::Complete)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->count();


                $transferFail = Transfer::where('status', TransferStatus::Failed)
                    ->where('user_id', $user->id)
                    ->limit(20)
                    ->get();

                $timerNextTransfer = Cache::get('timeNextTransfer') - time();

                $data = [
                    'hotWallets'            => $hotWallets,
                    'sumHotWallet'          => $sumHotWallet,
                    'countHotWallet'        => $countHotWallet,
                    'timerNextTransfer'     => $timerNextTransfer,
                    'averageTransferTime'   => $averageTransferTime ?? 10,
                    'transferStatsComplete' => $transferStatsComplete,
                    'transferComplete'      => $transferComplete,
                    'transferFail'          => $transferFail,
                    'transfersInProgress'   => $transfersInProgress,
                ];

                echo "data: " . json_encode($data) . "\n\n";
                ob_flush();
                flush();

                if (connection_aborted()) {
                    break;
                }
                usleep(50000);
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type'  => 'text/event-stream',
        ]);
    }
}
