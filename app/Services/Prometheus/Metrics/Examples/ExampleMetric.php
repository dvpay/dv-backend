<?php

namespace App\Services\Prometheus\Metrics\Examples;

use App\Services\Prometheus\Metrics\AbstractMetric;

class ExampleMetric extends AbstractMetric
{
    protected string $name = 'example_metric';
    protected string $help = 'Example Metric Help';
    protected array $labels = ['label1','label2'];

    // Prometheus::counterInc((new ExampleMetric())->getName(),4,['aaa','bbb']);

}