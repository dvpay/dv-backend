<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\Prometheus\Metrics\MetricInterface;

enum Metric implements MetricInterface
{
    case ProcessingCallbackReceived;
    case ProcessingHttpClientStats;
    case BackendHttpExecutionDurationTime;
    case BackendCommandExecutionDurationTime;
    case BackendScheduledCommandExecutionDurationTime;
    case BackendScheduledFailedAndSkippedCommands;
    case BackendTransactionCreated;
    case BackendUnconfirmedTransactionCreated;
    case BackendTronProcessingWalletEnergy;
    case BackendTronProcessingWalletEnergyLimit;
    case BackendTronProcessingWalletBandwidth;
    case BackendTronProcessingWalletBandwidthLimit;
    case CommonExternalHttpClientStats;

    public function getName(): string
    {
        return match($this) {
            self::ProcessingCallbackReceived => 'processing_callback_received',
            self::ProcessingHttpClientStats => 'processing_http_client_stats',
            self::BackendHttpExecutionDurationTime => 'backend_http_execution_duration_time',
            self::BackendCommandExecutionDurationTime => 'backend_command_execution_duration_time',
            self::BackendScheduledCommandExecutionDurationTime => 'backend_scheduled_command_execution_duration_time',
            self::BackendScheduledFailedAndSkippedCommands => 'backend_scheduled_failed_and_skipped_commands',
            self::BackendTransactionCreated => 'backend_transaction_created',
            self::BackendUnconfirmedTransactionCreated => 'backend_unconfirmed_transaction_created',
            self::BackendTronProcessingWalletEnergy => 'backend_tron_processing_wallet_energy',
            self::BackendTronProcessingWalletEnergyLimit => 'backend_tron_processing_wallet_energy_limit',
            self::BackendTronProcessingWalletBandwidth => 'backend_tron_processing_wallet_bandwidth',
            self::BackendTronProcessingWalletBandwidthLimit => 'backend_tron_processing_wallet_bandwidth_limit',
            self::CommonExternalHttpClientStats => 'common_external_http_client_stats',
        };

    }

    public function getType(): MetricType
    {
        return match($this) {
            self::ProcessingCallbackReceived => MetricType::Counter,
            self::ProcessingHttpClientStats => MetricType::Histogram,
            self::BackendHttpExecutionDurationTime => MetricType::Histogram,
            self::BackendCommandExecutionDurationTime => MetricType::Histogram,
            self::BackendScheduledCommandExecutionDurationTime => MetricType::Histogram,
            self::BackendScheduledFailedAndSkippedCommands => MetricType::Counter,
            self::BackendTransactionCreated => MetricType::Counter,
            self::BackendUnconfirmedTransactionCreated => MetricType::Counter,
            self::BackendTronProcessingWalletEnergy => MetricType::Gauge,
            self::BackendTronProcessingWalletEnergyLimit => MetricType::Gauge,
            self::BackendTronProcessingWalletBandwidth => MetricType::Gauge,
            self::BackendTronProcessingWalletBandwidthLimit => MetricType::Gauge,
            self::CommonExternalHttpClientStats => MetricType::Histogram,
        };
    }

    public function getHelp(): string
    {
        return match($this) {
            self::ProcessingCallbackReceived => 'Processing callback received counter grouped by type',
            self::ProcessingHttpClientStats => 'Processing http client stats',
            self::BackendHttpExecutionDurationTime => 'Backend HTTP execution duration time',
            self::BackendCommandExecutionDurationTime => 'Backend command execution duration time',
            self::BackendScheduledCommandExecutionDurationTime => 'Backend scheduled command execution duration time',
            self::BackendScheduledFailedAndSkippedCommands => 'Backend scheduled failed and skipped commands',
            self::BackendTransactionCreated => 'Backend transaction created',
            self::BackendUnconfirmedTransactionCreated => 'Backend unconfirmed transaction created',
            self::BackendTronProcessingWalletEnergy => 'Backend tron processing wallet energy',
            self::BackendTronProcessingWalletEnergyLimit => 'Backend tron processing wallet energy limit',
            self::BackendTronProcessingWalletBandwidth => 'Backend tron processing wallet bandwidth',
            self::BackendTronProcessingWalletBandwidthLimit => 'Backend tron processing wallet bandwidth limit',
            self::CommonExternalHttpClientStats => 'Common external http client stats',
        };
    }

    public function getLabels(): array
    {
        return match($this) {
            self::ProcessingCallbackReceived => ['type'],
            self::ProcessingHttpClientStats => ['path','method','status'],
            self::BackendHttpExecutionDurationTime => ['action','method'],
            self::BackendCommandExecutionDurationTime => ['command','result'],
            self::BackendScheduledCommandExecutionDurationTime => ['command'],
            self::BackendScheduledFailedAndSkippedCommands => ['command','result'],
            self::BackendTransactionCreated => ['type'],
            self::BackendTronProcessingWalletEnergy => ['owner_id'],
            self::BackendTronProcessingWalletEnergyLimit => ['owner_id'],
            self::BackendTronProcessingWalletBandwidth => ['owner_id'],
            self::BackendTronProcessingWalletBandwidthLimit => ['owner_id'],
            self::CommonExternalHttpClientStats => ['external_service','method','status'],
            default => []
        };
    }

    public function getBuckets(): array
    {
        return match($this) {
            self::BackendHttpExecutionDurationTime => [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0, 20.0, 30.0],
            self::ProcessingHttpClientStats => [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0, 20.0, 30.0],
            self::BackendCommandExecutionDurationTime => [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0, 20.0, 30.0,50.0,100.0,500.0,1000.0],
            self::BackendScheduledCommandExecutionDurationTime => [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0, 20.0, 30.0,50.0,100.0,500.0,1000.0],
            default => [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0]
        };
    }


}
