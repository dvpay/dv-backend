<?php

declare(strict_types=1);

namespace App\Services\PublicExplorer;

/**
 * Tronscan
 * https://tronscan.org/#/
 */
class Tronscan implements PublicExplorerContract
{
	/**
	 * @var string
	 */
	private static string $baseUrl = 'https://tronscan.io';

	/**
	 * @return string
	 */
	public function getBaseUrl(): string
	{
		return static::$baseUrl;
	}

	/**
	 * @param string $address
	 *
	 * @return string
	 */
	public function getAddressUrl(string $address): string
	{
		return static::$baseUrl . '/#/address/' . $address;
	}

	/**
	 * @param string $txId
	 *
	 * @return string
	 */
	public function getTransactionUrl(string $txId): string
	{
		return static::$baseUrl . '/#/transaction/' . $txId;
	}
}
