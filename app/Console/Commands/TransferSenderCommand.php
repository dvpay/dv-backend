<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\Transfer\TransferDto;
use App\Enums\TransferStatus;
use App\Models\Transfer as TransferModel;
use App\Services\Processing\Contracts\TransferContract;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 *
 */
class TransferSenderCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'transfer:sender {currency}';

    /**
     * @var string
     */
    protected $description = 'Command description';

    /**
     *
     */
    public const TronLimit = 30;

    /**
     * @param TransferContract $transferContract
     * @return void
     */
    public function handle(TransferContract $transferContract): void
    {
        $transfers = TransferModel::where('status', TransferStatus::Waiting->value)
            ->where('currency_id', $this->argument('currency'))
            ->oldest();

        if ($this->argument('currency') === 'USDT.Tron') {
            $transfers = $transfers->limit(self::TronLimit);
        }

         $transfers->each(function ($transfer) use ($transferContract) {
            $dto = new TransferDto([
                'uuid'        => Str::uuid(),
                'user'        => $transfer->user,
                'currency'    => $transfer->currency,
                'status'      => TransferStatus::Sending,
                'addressFrom' => $transfer->address_from,
                'addressTo'   => $transfer->address_to,
                'contract'    => $transfer->currency->contract_address
            ]);
            $transferContract->transferFromAddress($dto);

            $transfer->update([
                'status' => TransferStatus::Sending
            ]);
        });

    }
}
