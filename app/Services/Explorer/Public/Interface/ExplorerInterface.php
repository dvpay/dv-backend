<?php

namespace App\Services\Explorer\Public\Interface;

interface ExplorerInterface
{
    public function getAddressBalance(string $address): string;
}
