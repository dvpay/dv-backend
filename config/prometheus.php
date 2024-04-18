<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace to use as a prefix for all metrics.
    |
    | This will typically be the name of your project, eg: 'search'.
    |
    */

    'enabled' => env('PROMETHEUS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace to use as a prefix for all metrics.
    |
    | This will typically be the name of your project, eg: 'search'.
    |
    */

    'namespace' => env('PROMETHEUS_NAMESPACE', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Storage Adapter
    |--------------------------------------------------------------------------
    |
    | The storage adapter to use.
    |
    | Supported: "memory", "redis"
    |
    */

    'storage_adapter' => env('PROMETHEUS_STORAGE_ADAPTER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Storage Adapters
    |--------------------------------------------------------------------------
    |
    | The storage adapter configs.
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Allowed IP's
    |--------------------------------------------------------------------------
    |
    | Only these IP's will be allowed to visit the above urls.
    | All IP's are allowed when empty.
     */
    #TODO: add support of subnets
    'allowed_ips' => [
//         '192.168.65.1',
    ],

    'basicAuthLogin' =>  env('PROMETHEUS_BASIC_AUTH_LOGIN') ?? md5(mt_rand()),
    'basicAuthPassword' => env('PROMETHEUS_BASIC_AUTH_PASSWORD') ?? md5(mt_rand()),

    'storage_adapters' => [

        'redis' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'port' => env('REDIS_PORT', 6379),
            'timeout' => 0.1,
            'read_timeout' => 10,
            'persistent_connections' => false,
            'prefix' => env('PROMETHEUS_NAMESPACE', 'PROMETHEUS_'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Collectors
    |--------------------------------------------------------------------------
    |
    | The collectors specified here will be auto-registered in the exporter.
    |
    */

    'collectors' => [
        //\App\Services\Prometheus\Metrics\Examples\ExampleCollector::class,
        \App\Services\Prometheus\Metrics\QueueCollector::class,
        \App\Services\Prometheus\Metrics\VersionBackendCollector::class,
        \App\Services\Prometheus\Metrics\VersionProcessingCollector::class,
    ],
    'metrics' => \App\Enums\Metric::cases(),
//    'metrics' => [
//        \App\Services\Prometheus\Metrics\ExampleMetric::class,
//    ],

];
