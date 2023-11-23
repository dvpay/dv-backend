<?php

declare(strict_types=1);

namespace App\Services\Withdrawal\Rules;

use App\Enums\Blockchain;
use App\Enums\WithdrawalRuleType;
use App\Models\Wallet;
use App\Services\Processing\BalanceGetter;
use App\Services\Withdrawal\Contract\RuleExecutorContract;
use Illuminate\Support\Facades\Log;

class MinBalance implements RuleExecutorContract
{
    public function __construct(private readonly BalanceGetter $balanceGetter)
    {
    }

    public function getType(): WithdrawalRuleType
    {
        return WithdrawalRuleType::BalanceLimit;
    }

    public function execute(Wallet $wallet, mixed $value): bool
    {
        switch ($wallet->blockchain) {
            case Blockchain::Tron:
                $blockchain = 'tron';
                break;

            case Blockchain::Bitcoin:
                $blockchain = 'btc';
                break;
        }

        if (!$user = $wallet->user) {
            return false;
        }

        $balances = $this->balanceGetter->get($user);

        Log::error('[balances]', $balances);
        Log::error('[wallet]' . $wallet);

        foreach ($balances as $key => $value) {
            if ($key != $blockchain) {
                continue;
            }

            if ((float)$value >= (float)$wallet->withdrawal_min_balance) {
                return true;
            }
        }

        return false;
    }
}
