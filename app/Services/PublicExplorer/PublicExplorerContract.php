<?php

declare(strict_types=1);

namespace App\Services\PublicExplorer;

/**
 * PublicExplorerContract
 */
interface PublicExplorerContract
{
	/**
	 * @return string
	 */
	public function getBaseUrl(): string;

	/**
	 * @param string $address
	 *
	 * @return string
	 */
	public function getAddressUrl(string $address): string;

	/**
	 * @param string $txId
	 *
	 * @return string
	 */
	public function getTransactionUrl(string $txId): string;
}