<?php

declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Services\Processing\Contracts\MnemonicContract;

class MnemonicFake implements MnemonicContract
{
    public function generate(int $size = self::SIZE): string
    {
        return fake()->sentence($size);
    }
}
