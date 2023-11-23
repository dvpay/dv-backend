<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultResponseResource;

class SystemController extends Controller
{
    public function __invoke()
    {
        return new DefaultResponseResource(['status' => 'pong']);
    }
}
