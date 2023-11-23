<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ExchangeRequest;
use App\Models\ProcessingCallback;
use App\Models\ServiceLog;
use Illuminate\Console\Command;
use Throwable;

/**
 * ServiceLogClear.
 *
 * Deletes old service logs records older than 90 days.
 */
class ServiceLogClear extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'service:log:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clears old service monitoring logs.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 * @throws Throwable
	 */
	public function handle(): void
	{
		$time = time();

		ServiceLog::whereRaw('DATEDIFF(NOW(), created_at) > 30')->delete();
        ExchangeRequest::whereRaw('DATEDIFF(NOW(), created_at) > 14')->delete();
        ProcessingCallback::whereRaw('DATEDIFF(NOW(), created_at) > 14')->delete();

		$this->info('The command was successful! ' . time() - $time . ' s.');
	}
}
