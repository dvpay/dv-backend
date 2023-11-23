<?php

declare(strict_types=1);

namespace App\Dto;


/**
 * ServiceLogHistoryDto
 */
class ServiceLogHistoryDto extends ArrayDto
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
	 * @var int
	 */
	public readonly int $serviceId;
	/**
	 * @var string
	 */
	public readonly string $status;
}