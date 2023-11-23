<?php

declare(strict_types=1);

namespace App\Services\Withdrawal\Rules;

use App\Enums\TransactionType;
use App\Enums\WithdrawalInterval;
use App\Enums\WithdrawalRuleType;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\Withdrawal\Contract\RuleExecutorContract;
use DateTime;
use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
class Interval implements RuleExecutorContract
{
    public function getType(): WithdrawalRuleType
    {
        return WithdrawalRuleType::Interval;
    }

    public function execute(Wallet $wallet, mixed $value): bool
    {
        $interval = WithdrawalInterval::tryFrom($value);

        if ($interval == WithdrawalInterval::Zero) {
            return true;
        }

        $dt = new DateTime();
        $dt->sub($interval->toDateInterval());

        //return true if even one of currencies allow it
        foreach ($wallet->balances as $balance) {
            $cannot = Transaction::query()
                ->where('user_id', $wallet->user_id)
                ->where('type', TransactionType::Transfer)
                ->where('currency_id', $balance->currency_id)
                ->where('created_at', '>', $dt)
                ->exists();

            if (!$cannot) {
                return true;
            }
        }

        return false;
    }
}
