<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExchangeKeyType;
use Throwable;

class ExchangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'exchange_id',
        'request',
        'response',
    ];

    /**
     * @throws Throwable
     */
    public static function createLog(?int $userId, int $exchangeId, string $request, string $response)
    {
        $log = new ExchangeRequest([
            'user_id' => $userId,
            'exchange_id' => $exchangeId,
            'request' => $request,
            'response' => $response,
        ]);

        $log->saveOrFail();
    }
}