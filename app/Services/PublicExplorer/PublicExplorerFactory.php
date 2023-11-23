<?php

declare(strict_types=1);

namespace App\Services\PublicExplorer;

/**
 * PublicExplorerFactory
 */
class PublicExplorerFactory
{
	/**
	 * @param string $currencyId
	 *
	 * @return Blockchain|Tronscan|null
	 */
	public static function makeExplorer(string $currencyId): Blockchain|Tronscan|null
	{
		return match ($currencyId) {
			'BTC.Bitcoin' => new Blockchain(),
			'USDT.Tron' => new Tronscan(),
			default => null
		};
	}
}
