<?php

declare(strict_types=1);

namespace App\Http\Resources;

class DefaultResponseResource extends BaseResource
{
    public $additional = ['errors' => []];

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null $wrap
     */
    public static $wrap = 'result';
}