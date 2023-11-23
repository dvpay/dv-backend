<?php
declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payer;
use App\Services\Processing\Dto\Watch;
use App\Enums\Blockchain;
use App\Services\Processing\Dto\WatchPromise;

interface AddressContract
{
    public function generate(Blockchain $blockchain, string $owner): string;

    public function generateAndWatch(Watch $watch, Invoice $invoice): WatchPromise;

    public function watch(Watch $watch): WatchPromise;

    public function getAll(string $ownerId): array;

    public function getStaticAddress(Currency $currency, Payer $payer, string $ownerId): array;
}