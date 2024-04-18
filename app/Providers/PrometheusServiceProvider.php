<?php

namespace App\Providers;

use App\Services\Prometheus\FakePrometheusExporter;
use App\Services\Prometheus\Metrics\MetricInterface;
use App\Services\Prometheus\PrometheusExporter;
use App\Services\Prometheus\PrometheusExporterInterface;
use App\Services\Prometheus\StorageAdapterFactory;
use Illuminate\Support\Str;
use Prometheus\Storage\Adapter;
use Prometheus\CollectorRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class PrometheusServiceProvider extends ServiceProvider
{

    public function boot()
    {
        if(!config('prometheus.enabled')) {
            return;
        }

        $exporter = $this->app->make(PrometheusExporterInterface::class);
        foreach (config('prometheus.collectors') as $class) {
            $collector = $this->app->make($class);
            $exporter->registerCollector($collector);
        }

        $metricsEnum = config('prometheus.metrics', []);

        foreach ($metricsEnum as $metric) {

            if (!is_object($metric) && class_exists($metric)) {
                $metric = new $metric;
            }

            if(!$metric instanceof MetricInterface) {
                continue;
            }

            $exporter->registerMetric($metric);
        }
    }

    public function register()
    {

        if(!config('prometheus.enabled')) {
            $this->app->scoped(PrometheusExporterInterface::class, function () {
                return new FakePrometheusExporter();
            });

            $this->app->alias(PrometheusExporterInterface::class, 'prometheus');

            return;
        }

        $this->app->scoped(PrometheusExporterInterface::class, function ($app) {
            $adapter = $app['prometheus.storage_adapter'];
            $prometheus = new CollectorRegistry($adapter,false);
            $namespace = Str::slug(
                title: config('prometheus.namespace'),
                separator: "_",
                dictionary:  [
                    '@' => '_at_',
                    '.' => '_',
                ]);

            return new PrometheusExporter($namespace, $prometheus);
        });

        $this->app->alias(PrometheusExporterInterface::class, 'prometheus');

        $this->app->bind('prometheus.storage_adapter_factory', function () {
            return new StorageAdapterFactory();
        });

        $this->app->bind(Adapter::class, function ($app) {
            $factory = $app['prometheus.storage_adapter_factory']; /** @var StorageAdapterFactory $factory */
            $driver = config('prometheus.storage_adapter');
            $configs = config('prometheus.storage_adapters');
            $config = Arr::get($configs, $driver, []);
            return $factory->make($driver, $config);
        });
        $this->app->alias(Adapter::class, 'prometheus.storage_adapter');
    }

}