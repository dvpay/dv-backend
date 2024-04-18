<?php

namespace App\Services\Prometheus\Metrics;

use App\Enums\MetricType;

interface MetricInterface
{
    public function getName(): string;
    public function getHelp(): string;
    public function getType(): MetricType;
    public function getLabels(): array;
    public function getBuckets(): array;

}