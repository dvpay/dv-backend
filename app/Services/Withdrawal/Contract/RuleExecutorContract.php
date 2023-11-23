<?php

declare(strict_types=1);

namespace App\Services\Withdrawal\Contract;

use App\Enums\WithdrawalRuleType;
use App\Models\Wallet;

interface RuleExecutorContract
{
    public function getType(): WithdrawalRuleType;

    public function execute(Wallet $wallet, mixed $value): bool;
}
