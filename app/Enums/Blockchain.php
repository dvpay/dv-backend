<?php

declare(strict_types=1);

namespace App\Enums;

enum Blockchain: string
{
    case Tron = 'tron';
    case Bitcoin = 'bitcoin';
    case Ethereum = 'ethereum';

    public function getNativeToken(): CurrencySymbol
    {
        return match ($this) {
            self::Tron => CurrencySymbol::TRX,
            self::Bitcoin => CurrencySymbol::BTC,
            self::Ethereum => CurrencySymbol::ETH,
        };
    }

    public function getShortBlockchain(): string
    {
        return match ($this) {
            self::Tron => 'tron',
            self::Bitcoin => 'btc',
            self::Ethereum => 'eth',
        };
    }


    public function getChain(): string
    {
        return match ($this) {
            self::Tron => 'trc20usdt',
            self::Bitcoin => 'btc',
            self::Ethereum => 'eth',
        };
    }

    public function getCurrency(): string
    {
        return match ($this) {
            self::Tron => 'usdt',
            self::Bitcoin => 'btc',
            self::Ethereum => 'eth',
        };
    }


    public function getExplorerUrl(): string
    {
        return match ($this) {
            self::Tron => 'https://tronscan.org/#/transaction',
            self::Bitcoin => 'https://www.blockchain.com/btc/tx',
        };
    }

    public function getExplorerUrlAddress(): string
    {
        return match ($this) {
            self::Tron => 'https://tronscan.org/#/address',
            self::Bitcoin => 'https://www.blockchain.com/explorer/addresses/btc',
            default => ''
        };
    }

    public function getWalletExplorerUrl(): string
    {
        return match ($this) {
            self::Tron => 'https://apilist.tronscanapi.com/api/accountv2?address=',
            self::Bitcoin => 'https://blockchain.info/rawaddr/',
        };
    }

    public function getAddressValidationRegex(): string
    {
        return match ($this) {
            self::Tron => '/T[A-Za-z1-9]{33}/',
            self::Bitcoin => '/^(bc1|[13])[a-zA-HJ-NP-Z0-9]{25,62}$/',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
