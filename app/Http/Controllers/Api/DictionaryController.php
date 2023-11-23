<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\DefaultResponseResource;
use App\Services\Dictionary\DictionaryService;
use Illuminate\Cache\Repository;
use Illuminate\Http\JsonResponse;

class DictionaryController extends ApiController
{
    public function __construct(
        private readonly DictionaryService $dictionaryService,
        private readonly Repository        $cache
    )
    {
    }

    public function dictionaries(): DefaultResponseResource
    {
        $key = 'dictionaries';
        $dictionaries = $this->cache->get($key, function () use ($key) {
            $dictionaries = $this->dictionaryService->dictionaries();

            $this->cache->set($key, $dictionaries, now()->addHour());

            return $dictionaries;
        });

        return new DefaultResponseResource($dictionaries);
    }
}