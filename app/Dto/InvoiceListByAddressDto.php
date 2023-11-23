<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * InvoiceAddressesListDto
 */
class InvoiceListByAddressDto extends ArrayDto
{
    /**
     * @var string
     */
    public readonly string $address;
    /**
     * @var array|null
     */
    public readonly ?array $stores;
    /**
     * @var int
     */
    public readonly int $page;
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
}