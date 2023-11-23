<?php

declare(strict_types=1);

namespace App\Services\Currency;

use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Models\Notification\Notification;
use Illuminate\Support\Facades\Log;

/**
 * CurrencyService
 */
class CurrencyRateCheck
{
    /**
     * @param CurrencyStore $currencyStore
     * @param int $maxRateDifference
     */
    public function __construct(
        private readonly CurrencyStore $currencyStore,
        private readonly int           $maxRateDifference
    )
    {
    }

    /**
     * @param RateSource $rateSource
     * @param CurrencySymbol $from
     * @param CurrencySymbol $to
     * @param string $currentRate
     * @return void
     */
    public function checkRate(RateSource $rateSource, CurrencySymbol $from, CurrencySymbol $to, string $currentRate): void
    {
        $data = $this->currencyStore->get($rateSource, $from, $to);

        if (!$data) {
            return;
        }

        $difference = bcsub($currentRate, $data['rate']);
        $inPercent = bcmul(bcdiv($data['rate'], '100'), $difference);

        if ($inPercent >= $this->maxRateDifference) {

            $notification = Notification::where('slug', '=', 'sharpExchangeRateChange')->first();

            $notificationDate = [
                'from'        => $from->value,
                'to'          => $to->value,
                'oldRate'     => $data['rate'],
                'currentRate' => $currentRate,
                'difference'  => $difference
            ];

            $notification->users->each(fn($user) => $user->notifySharpRate($notificationDate));

        }
    }
}