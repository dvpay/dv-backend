<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Models\WalletDto;
use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Models\Currency;
use App\Models\Store;
use App\Models\StoreApiKey;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;

class CurrencyRepository
{
    public function getFiatCurrencyByCode(string $code): ?Currency
    {
        return Currency::where([
            ['code', $code],
            ['is_fiat', true],
        ])->first();
    }

    public function getById(string $id): ?Currency
    {
        return Currency::find($id);
    }
}