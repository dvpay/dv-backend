<?php

namespace App\Services\HotWallet;

use App\Dto\HotWallet\HotWalletDto;
use App\Dto\HotWallet\HotWalletsListDto;
use App\Models\HotWallet;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

interface HotWalletServiceInterface
{
    public function userHotWallets(HotWalletsListDto $dto);

    public function storeHotWallet(HotWalletDto $dto): HotWallet;

    public function getSummary(Authenticatable $user): Collection;
}
