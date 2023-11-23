<?php

declare(strict_types=1);

namespace App\Services\Processing\Contracts;

interface MnemonicContract
{
    public const SIZE = 12;

    /**
     * generates a seed phrase
     *
     * @param int $size
     * @return string
     */
    public function generate(int $size = self::SIZE): string;
}
