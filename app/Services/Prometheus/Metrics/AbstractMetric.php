<?php

namespace App\Services\Prometheus\Metrics;

use App\Enums\MetricType;

class AbstractMetric implements MetricInterface
{
    protected string $name;
    protected string $help;
    protected MetricType $type = MetricType::Counter;
    protected array $labels = [];
    protected array $buckets = [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0];

    public function getName(): string
    {
        return $this->name;
    }
    public function getHelp():string
    {
        return $this->help;
    }
    public function getType(): MetricType
    {
        return $this->type;
    }
    public function getLabels(): array
    {
        return $this->labels;
    }
    public function getBuckets(): array
    {
        return $this->buckets;
    }
}