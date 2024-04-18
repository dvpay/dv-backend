<?php

namespace App\Services\Prometheus;

interface PrometheusExporterInterface
{

    public function counterInc(string $name, int $count = 1, array $labels = []);
    public function gaugeSet(string $name, float $value = 0, array $labels = []);
    public function histogramObserve(string $name, float $value = 0.0,array $labels = []);

    public function export(): array;

}