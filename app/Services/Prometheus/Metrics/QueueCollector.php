<?php

namespace App\Services\Prometheus\Metrics;

use App\Enums\Queue;
use App\Services\Prometheus\PrometheusExporter;
use App\Services\Prometheus\PrometheusExporterInterface;
use Prometheus\Gauge;

class QueueCollector implements CollectorInterface
{

    protected Gauge $queueGauge;

    /**
     * Return the name of the collector.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'backend_queue_sizes';
    }

    /**
     * @param PrometheusExporter $exporter
     */
    public function registerMetrics(PrometheusExporterInterface $exporter): void
    {
        $this->queueGauge = $exporter->registerGauge(
            'backend_queue_sizes',
            'The total number of queued messages.',
            ['queue']
        );
    }

    public function collect(): void
    {

        foreach (Queue::cases() as $queue) {
            $this->queueGauge->set(\Queue::size($queue->value),[$queue->value]);
        }
    }
}