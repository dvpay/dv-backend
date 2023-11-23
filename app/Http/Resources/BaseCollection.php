<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseCollection extends ResourceCollection
{
    public $additional = ['errors' => []];

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null $wrap
     */
    public static $wrap = 'result';

}
