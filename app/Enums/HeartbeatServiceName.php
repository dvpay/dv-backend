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
    case CronInvoiceUpdateStatus = 'cronInvoiceUpdateStatus';
    case CronWithdrawal = 'cronWithdrawal';
    case CronUserInvoiceAddressUpdate = 'cronUserInvoiceAddressUpdate';
    case CronProcessingStatusCheck = 'cronProcessingStatusCheck';
    case CronExplorerStatusCheck = 'cronExplorerStatusCheck';
    case CronNodeVersionStatus = 'cronNodeVersionStatus';
    case CronNodeVersionControl = 'cronNodeVersionControl';
    case CronExchangeWithdrawal = 'cronExchangeWithdrawal';

    case ServiceBinance = 'serviceBinance';
    case ServiceCoinGate = 'serviceCoinGate';
    case ServiceProcessing = 'serviceProcessing';
    case ServiceBitcoinExplorer = 'serviceBitcoinExplorer';
    case ServiceTronExplorer = 'serviceTronExplorer';

    case NodeBitcoin = 'nodeBitcoin';
    case NodeTron = 'nodeTron';
    case NodeBitcoinVersion = 'nodeBitcoinVersion';
    case NodeTronVersion = 'nodeTronVersion';

	/**
	 * @return HeartbeatServiceName[]
	 */
	public static function forDashboard(): array
    {
        return [
            HeartbeatServiceName::System,
            HeartbeatServiceName::CronCacheCurrencyRate,
            HeartbeatServiceName::CronWithdrawal,
            HeartbeatServiceName::ServiceProcessing,
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
            HeartbeatServiceName::CronInvoiceUpdateStatus => 'Cron invoice update status',
            HeartbeatServiceName::CronWithdrawal => 'Cron withdrawal',
            HeartbeatServiceName::CronUserInvoiceAddressUpdate => 'Cron user invoice address update',
            HeartbeatServiceName::CronProcessingStatusCheck => 'Cron check processing status',
            HeartbeatServiceName::CronExplorerStatusCheck => 'Cron check explorer status',
            HeartbeatServiceName::CronNodeVersionStatus => 'Cron check node status',
            HeartbeatServiceName::CronNodeVersionControl => 'Cron node version control',
            HeartbeatServiceName::CronExchangeWithdrawal => 'Cron exchange withdrawal',

            HeartbeatServiceName::ServiceBinance => 'Service Binance',
            HeartbeatServiceName::ServiceCoinGate => 'Service CoinGate',
            HeartbeatServiceName::ServiceProcessing => 'Service blockchain processing',
            HeartbeatServiceName::ServiceBitcoinExplorer => 'Service Bitcoin Explorer',
            HeartbeatServiceName::ServiceTronExplorer => 'Service Tron Explorer',

            HeartbeatServiceName::NodeBitcoin => 'Node Bitcoin',
            HeartbeatServiceName::NodeTron => 'Node Tron',
            HeartbeatServiceName::NodeBitcoinVersion => 'Node Bitcoin version',
            HeartbeatServiceName::NodeTronVersion => 'Node Tron version',
        };
    }
}
