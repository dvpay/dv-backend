<?php

declare(strict_types=1);

namespace App\Enums;

enum MetricType :string
{
    case Counter = 'counter';
    case Gauge = 'gauge';
    case Histogram = 'histogram';

}
