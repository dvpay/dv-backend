<?php

namespace App\Services\Prometheus\Metrics;

use App\Services\Prometheus\PrometheusExporter;
use App\Services\Prometheus\PrometheusExporterInterface;
use Prometheus\Gauge;

class VersionBackendCollector implements CollectorInterface
{

    protected Gauge $backendVersionGauge;

    /**
     * Return the name of the collector.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'backend_version';
    }

    /**
     * @param PrometheusExporter $exporter
     */
    public function registerMetrics(PrometheusExporterInterface $exporter): void
    {
        $this->backendVersionGauge = $exporter->registerGauge(
            'backend_version',
            'Backend version.',
            ["version"]
        );
    }

    public function collect(): void
    {
        $this->backendVersionGauge->set(1,[config('app.version')]);
    }
}