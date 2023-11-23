<?php
declare(strict_types=1);

namespace App\Enums;

enum ExchangeKeyType: string
{
    case AccessKey = 'accessKey';
    case SecretKey = 'secretKey';

    public function title(): string
    {
        return match ($this) {
            ExchangeKeyType::AccessKey => 'Access key',
            ExchangeKeyType::SecretKey => 'Secret key',
        };
    }
}