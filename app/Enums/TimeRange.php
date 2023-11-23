<?php

declare(strict_types=1);

namespace App\Enums;

use DateInterval;

/**
 * Names of time ranges.
 */
enum TimeRange: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
    case Today = 'today';
    case Yesterday = 'yesterday';

	/**
	 * @return DateInterval
	 */
	public function interval(): DateInterval
    {
        return match ($this)
        {
            TimeRange::Day => new DateInterval('P1D'),
            TimeRange::Week => new DateInterval('P1W'),
            TimeRange::Month => new DateInterval('P1M'),
            TimeRange::Year => new DateInterval('P1Y'),
        };
    }

	/**
	 * @return DateInterval
	 */
	public function step(): DateInterval
    {
        return match ($this)
        {
            TimeRange::Week => new DateInterval('P1D'),
            TimeRange::Month => new DateInterval('P1W'),
            TimeRange::Year => new DateInterval('P1M'),
        };
    }
}
