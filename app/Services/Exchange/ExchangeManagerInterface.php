<?php

namespace App\Services\Exchange;

use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

interface ExchangeManagerInterface
{
    public function make(ExchangeServiceEnum $service, Authenticatable|User $user);
}