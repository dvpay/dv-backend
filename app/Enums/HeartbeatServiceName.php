<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * HeartbeatServiceName enums.
 */
enum HeartbeatServiceName: string
{
    case System = 'system';

    case CronCacheCurrencyRate = 'cronCacheCurrencyRate';
    case CronProcessingStatusCheck = 'cronProcessingStatusCheck';
    case CronExchangeWithdrawal = 'cronExchangeWithdrawal';

    case ServiceBinance = 'serviceBinance';
    case ServiceCoinGate = 'serviceCoinGate';

	/**
	 * @return HeartbeatServiceName[]
	 */
	public static function forDashboard(): array
    {
        return [
            HeartbeatServiceName::System,
            HeartbeatServiceName::CronCacheCurrencyRate,
        ];
    }

	/**
	 * @return string
	 */
	public function title(): string
    {
        return match ($this) {
            HeartbeatServiceName::System => 'System',

            HeartbeatServiceName::CronCacheCurrencyRate => 'Cron cache currency rate',
            HeartbeatServiceName::CronProcessingStatusCheck => 'Cron check processing status',
            HeartbeatServiceName::CronExchangeWithdrawal => 'Cron exchange withdrawal',

            HeartbeatServiceName::ServiceBinance => 'Service Binance',
            HeartbeatServiceName::ServiceCoinGate => 'Service CoinGate',
        };
    }
}
