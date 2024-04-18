<?php
declare(strict_types=1);

namespace App\Services\Processing;

use App\Dto\Transfer\TransferDto;
use App\Enums\TransferStatus;
use App\Models\Transfer;
use App\Models\User;

class TransferService
{

    /**
     * @param TransferDto $dto
     * @return Transfer
     */
    public function createTransfer(TransferDto $dto): Transfer
    {
        return Transfer::create([
            'uuid'         => $dto->uuid,
            'user_id'      => $dto->user->id,
            'kind'         => $dto->kind,
            'currency_id'  => $dto->currency->id,
            'status'       => $dto->status,
            'address_from' => $dto->addressFrom,
            'address_to'   => $dto->addressTo,
            'amount'       => $dto->amount,
            'amount_usd'   => $dto->amountUsd,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function updateStatus(string $identification, TransferStatus $status, ?string $message)
    {
        return Transfer::where('uuid', $identification)
            ->update([
                'status'  => $status->value,
                'message' => $message],
            );
    }

    /**
     * @param int $minute
     * @return void
     */
    #TODO: Check this method after adding new kind of transfers TransferKind::TransferFromProcessing
    public function expiredTransfer(int $minute): void
    {
        $date = now()->subMinutes($minute);
        Transfer::where('status', TransferStatus::Waiting)
            ->where('created_at', '<', $date)
            ->update(['status' => TransferStatus::Failed]);
    }

    /**
     * @param int $day
     * @return void
     */
    #TODO: Check this method after adding new kind of transfers TransferKind::TransferFromProcessing
    public function deleteOldTransfer(int $day): void
    {
        $date = now()->subDays($day);
        Transfer::where('created_at', '<', $date)
            ->delete();
    }

    /**
     * @return int
     */
    #TODO: Check this method after adding new kind of transfers TransferKind::TransferFromProcessing
    public function getTransferInWorkCount(): int
    {
        return Transfer::where('status', TransferStatus::Waiting->value)->count();
    }

    /**
     * @param User $user
     * @return int
     */
    #TODO: Check this method after adding new kind of transfers TransferKind::TransferFromProcessing
    public function getTransferInWorkCountByUser(User $user): int
    {
        return Transfer::where('user_id', $user->id)
            ->whereIn('status', [TransferStatus::Waiting->value, TransferStatus::Sending->value])
            ->count();
    }
}
