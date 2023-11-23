<?php

declare(strict_types=1);

namespace App\Repositories;


use App\Models\RateSource;
use Illuminate\Database\Eloquent\Collection;

class RateSourceRepository
{
    public function getActualRateSources(): ?Collection
    {
        return RateSource::where('uri', '!=', '')->get();
    }

    public function getByName(string $name): ?RateSource
    {
        return RateSource::where('name', $name)->first();
    }
}
