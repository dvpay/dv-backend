<?php

declare(strict_types=1);

namespace App\Services\Withdrawal;

use App\Models\Wallet;
use App\Services\Withdrawal\Contract\RuleExecutorContract;

/**
 * WithdrawalRuleManager
 */
class WithdrawalRuleManager
{
    /**
     * @param RuleExecutorContract[] $executors
     */
    public function __construct(
        private readonly WithdrawalSettingService $withdrawalSettingsService,
        private readonly array                     $executors
    )
    {
    }

    /**
     * @param Wallet $wallet
     * @return bool
     */
    public function execute(Wallet $wallet): bool
    {
        $dto = $this->withdrawalSettingsService->get($wallet);
        if (!$dto->enabled) {
            return false;
        }

        foreach ($dto->getRules() as $type => $value) {
            $executor = $this->executors[$type] ?? null;
            if (!$executor?->execute($wallet, $value)) {
                return false; // return false immediately if any iteration returns false
            }
        }

        return true;
    }
}
