<?php

declare(strict_types=1);

namespace App\Http\Resources;

class ExceptionResource extends BaseResource
{
    public $additional = ['result' => []];

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null $wrap
     */
    public static $wrap = 'errors';
}