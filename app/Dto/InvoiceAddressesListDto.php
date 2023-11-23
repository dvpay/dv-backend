<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\User;

/**
 * InvoiceAddressesListDto
 */
class InvoiceAddressesListDto extends ArrayDto
{
	/**
	 * @var string
	 */
	public readonly string $page;
	/**
	 * @var int
	 */
	public readonly int $perPage;
	/**
	 * @var string
	 */
	public readonly string $sortField;
	/**
	 * @var string
	 */
	public readonly string $sortDirection;
	/**
	 * @var string
	 */
	public readonly string $filterField;
	/**
	 * @var string
	 */
	public readonly string $filterValue;
	/**
	 * @var bool
	 */
	public readonly bool $hideEmpty;
	/**
	 * @var User
	 */
	public readonly User $user;
    /**
     * @var array|null
     */
    public readonly ?array $stores;
}