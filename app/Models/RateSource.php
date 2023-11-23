<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RateSource as RateSourceEnum;

class RateSource extends Model
{
    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $casts = [
        'name' => RateSourceEnum::class,
    ];
}