<?php

declare(strict_types=1);

namespace App\Services\PublicExplorer;

/**
 * Blockchain.com
 *
 * https://www.blockchain.com/explorer
 */
class Blockchain implements PublicExplorerContract
{
	/**
	 * @var string
	 */
	private static string $baseUrl = 'https://www.blockchain.com/explorer';

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
		return static::$baseUrl . '/addresses/btc/' . $address;
	}

	/**
	 * @param string $txId
	 *
	 * @return string
	 */
	public function getTransactionUrl(string $txId): string
	{
		return static::$baseUrl . '/transactions/btc/' . $txId;
	}
}