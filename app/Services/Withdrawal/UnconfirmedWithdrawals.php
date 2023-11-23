<?php

namespace App\Services\Withdrawal;

use App\Enums\TransactionType;
use App\Models\Transaction;
use GuzzleHttp\Client;
use Log;
use Throwable;

class UnconfirmedWithdrawals
{
    public function __construct(private readonly Client $client)
    {
    }

    public function get(string $storeId): array
    {
        $weekAgo = new \DateTime('-1 week');

        $transactions = Transaction::query()
            ->where('type', TransactionType::Transfer)
            ->where('store_id', $storeId)
            ->where('currency_id', 'BTC.Bitcoin')
            ->where('created_at', '>', $weekAgo)
            ->get()
            ->all();

        if (count($transactions) === 0) {
            return [];
        }

        $hashes = array_column($transactions, 'tx_id');

        $hashes = array_chunk($hashes, 5);
        $res = [];

        foreach ($hashes as $items) {
            $res[] = $this->doRequest($items);
        }

        $res = array_merge(...$res);

        return array_filter($transactions, function (Transaction $tx) use ($res) {
            $confirmations = (int) ($res[$tx->tx_id] ?? 0);
            return $confirmations === 0;
        });
    }

    private function doRequest(array $hashes): array
    {
        try {
            $res = $this->client->request('GET', '/transactions/confirmations', ['query' => ['hash' => implode(',', $hashes)]]);
            $body = json_decode((string)$res->getBody(), true);

            return $body;
        } catch (\Throwable $e) {
            Log::error("cannot do request for get confirmations", [
                'exception' => $e->getMessage(),
            ]);
            return [];
        }
    }
}