<?php

namespace App\Services\Prometheus\Metrics;

use App\Services\Dictionary\DictionaryService;
use App\Services\Prometheus\PrometheusExporter;
use App\Services\Prometheus\PrometheusExporterInterface;
use Prometheus\Gauge;

class VersionProcessingCollector implements CollectorInterface
{

    public function __construct(protected DictionaryService $dictionaryService)
    {
    }

    protected Gauge $processingVersionGauge;

    /**
     * Return the name of the collector.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'processing_version';
    }

    /**
     * @param PrometheusExporter $exporter
     */
    public function registerMetrics(PrometheusExporterInterface $exporter): void
    {
        $this->processingVersionGauge = $exporter->registerGauge(
            'processing_version',
            'Processing version.',
            ["version"]
        );
    }

    public function collect(): void
    {
        $version = $this->dictionaryService->processingVersion()?->release ?? 'unknown';
        $this->processingVersionGauge->set(1,[$version]);
    }
}