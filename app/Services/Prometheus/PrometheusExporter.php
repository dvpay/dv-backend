<?php

namespace App\Services\Prometheus;

use App\Enums\MetricType;
use App\Services\Prometheus\Metrics\CollectorInterface;
use App\Services\Prometheus\Metrics\MetricInterface;
use Illuminate\Support\Facades\Log;
use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;

class PrometheusExporter implements PrometheusExporterInterface
{
    protected string $namespace;
    protected CollectorRegistry $prometheus;
    protected array $collectors = [];

    public function __construct(string $namespace, CollectorRegistry $prometheus, array $collectors = [])
    {
        $this->namespace = $namespace;
        $this->prometheus = $prometheus;

        foreach ($collectors as $collector) {
            /* @var CollectorInterface $collector */
            $this->registerCollector($collector);
        }
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function registerCollector(CollectorInterface $collector): void
    {
        $name = $collector->getName();

        if (!isset($this->collectors[$name])) {
            $this->collectors[$name] = $collector;

            $collector->registerMetrics($this);
        }
    }

    public function registerMetric(MetricInterface $metric): void
    {
        $name = $metric->getName();
        $help = $metric->getHelp();
        $labels = $metric->getLabels();
        $buckets = $metric->getBuckets();

        match ($metric->getType()) {
            MetricType::Counter => $this->registerCounter($name, $help, $labels),
            MetricType::Gauge => $this->registerGauge($name, $help, $labels),
            MetricType::Histogram => $this->registerHistogram($name, $help, $labels, $buckets),
            default => throw new \InvalidArgumentException()
        };
    }

    /**
     * Register a counter.
     *
     * @see https://prometheus.io/docs/concepts/metric_types/#counter
     */
    public function registerCounter(string $name, string $help, array $labels = []): Counter
    {
        return $this->prometheus->registerCounter($this->namespace, $name, $help, $labels);
    }

    public function counterInc(string $name, int $count = 1,array $labels = [])
    {
        try {
            $this->prometheus->getCounter($this->namespace, $name)?->incBy($count,$labels);
        } catch (\Exception $e) {
            Log::error('Can\'t write metric ' . $name . ' ' . $e->getMessage());
        }

    }

    public function histogramObserve(string $name, float $value = 0.0,array $labels = [])
    {
        try {
            $this->prometheus->getHistogram($this->namespace, $name)?->observe($value,$labels);
        } catch (\Exception $e) {
            Log::error('Can\'t write metric ' . $name . ' ' . $e->getMessage());
        }

    }

    public function gaugeSet(string $name, float $value = 0, array $labels = [])
    {
        try {
            $this->prometheus->getGauge($this->namespace, $name)?->set($value,$labels);
        } catch (\Exception $e) {
            Log::error('Can\'t write metric ' . $name . ' ' . $e->getMessage());
        }

    }

    /**
     * Register a gauge.
     *
     * @see https://prometheus.io/docs/concepts/metric_types/#gauge
     */
    public function registerGauge(string $name, string $help, array $labels = []): Gauge
    {
        return $this->prometheus->registerGauge($this->namespace, $name, $help, $labels);
    }

    public function getGauge($name): Gauge
    {
        return $this->prometheus->getGauge($this->namespace, $name);
    }

    /**
     * Register a histogram.
     *
     * @see https://prometheus.io/docs/concepts/metric_types/#histogram
     */
    public function registerHistogram(string $name, string $help, array $labels = [], array $buckets = null): Histogram
    {
        return $this->prometheus->registerHistogram($this->namespace, $name, $help, $labels, $buckets);
    }

    public function getHistogram($name): Histogram
    {
        return $this->prometheus->getHistogram($this->namespace, $name);
    }

    /**
     * Export the metrics from all collectors.
     *
     * @return \Prometheus\MetricFamilySamples[]
     */
    public function export(): array
    {
        foreach ($this->collectors as $collector) {
            /* @var CollectorInterface $collector */
            $collector->collect();
        }

        return $this->prometheus->getMetricFamilySamples();
    }
}