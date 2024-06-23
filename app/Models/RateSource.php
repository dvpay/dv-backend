<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RateSource as RateSourceEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RateSource extends Model
{
    use HasFactory;

    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $casts = [
        'name' => RateSourceEnum::class,
    ];
}