<?php
declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Dto\ProcessingTransactionInfoDto;
use App\Enums\Blockchain;
use App\Services\Processing\Contracts\TransactionContract;
use DateTime;

class TransactionFake implements TransactionContract
{
    public function info(string $txId): ProcessingTransactionInfoDto
    {
//        $transaction = Transaction::where('tx_id', $txId)
//            ->firstOrFail();

        return new ProcessingTransactionInfoDto([
            'txId'            => 'f4f4f4f4fs',
            'amount'          => 100,
            'time'            => (new DateTime())->format(DATE_ATOM),
            'blockchain'      => Blockchain::Tron,
            'contractAddress' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            'sender'          => fake()->uuid(),
            'receiver'        => 'TKn5GuNb62KgQh7SLXznUrP33Naea0323d71',
            'payerId'         => '7ef76a4c-685b-4e0c-8a88-a9fd8b94c215',
            'confirmations'   => 10,
//            'watches' => [
//                fake()->uuid(),
//                fake()->uuid(),
//                fake()->uuid(),
//            ],
        ]);
    }

    public function getTransactionByAddress(string $ownerId, string $address): array
    {
        return [
            [
                "txId"            => "4ddd8bce0df64a2ff4be6d82088146b07783a8629913b5a50796ae4557e0e8e3",
                "from"            => "",
                "to"              => "1Ho1Z4328GZDT92ixgzAHQN7FAUg2ubJNP",
                "currency"        => "btc",
                "contractAddress" => null,
                "type"            => "deposit",
                "createdAt"       => "2023-07-03T13:46:07-04:00",
                "confirmations"   => 0,
            ],

            [
                "txId"            => "fbebeb838d48c00f0bb05593e61808a075877649df7733f44d6ef30dcd2cf9b7",
                "from"            => "1Ho1Z4328GZDT92ixgzAHQN7FAUg2ubJNP",
                "to"              => "bc1q0as2ldya76v56ut38z9dpes6srr92ruu9wt4g3",
                "currency"        => "btc",
                "contractAddress" => null,
                "type"            => "transfer",
                "createdAt"       => "2023-09-12T08:28:21.575144-04:00",
                "confirmations"   => 0,
            ],
        ];
    }
}
