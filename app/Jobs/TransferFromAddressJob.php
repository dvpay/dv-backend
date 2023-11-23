<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Dto\Transfer\TransferDto;
use App\Services\Processing\Contracts\TransferContract;
use App\Services\Processing\TransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * TransferFromAddressJob
 */
class TransferFromAddressJob implements ShouldQueue, ShouldBeUnique
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * @param TransferDto $dto
	 */
	public function __construct(
		private readonly TransferDto $dto,
	)
	{
	}

	/**
	 * @var int
	 */
	public int $uniqueFor = 3600;

	/**
	 * @return Repository
	 */
	public function uniqueVia(): Repository
	{
		return Cache::driver('redis');
	}

	/**
	 * @return string
	 */
	public function uniqueId(): string
	{
		return $this->dto->addressFrom;
	}

	/**
	 * @param TransferContract $transferContract
	 *
	 * @return void
	 */
	public function handle(TransferContract $transferContract, TransferService $transferService): void
	{
		try {
			$transferContract->transferFromAddress($this->dto);
            $transferService->createTransfer($this->dto);

        } catch (\Throwable $exception) {

		}
	}
}
