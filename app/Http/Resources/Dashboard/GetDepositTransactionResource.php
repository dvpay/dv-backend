<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use App\Http\Resources\BaseResource;
use App\Models\Currency;
use DateTime;
use Exception;
use Illuminate\Http\Request;

class GetDepositTransactionResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        return [
            'date' => $this->created_at->format(DATE_ATOM),
            'invoiceId' => $this->invoiceId,
            'custom' => $this->custom,
            'description' => $this->description,
            'storeName' => $this->storeName,
            'amountUsd' => $this->amountUsd,
            'amount' => $this->amount,
            'tx' => $this->tx,
            'explorerLink' => $this->getExplorerUrl($this->currencyId, $this->tx),
            'currencyId' => $this->currencyId,
        ];
    }

    private function getExplorerUrl(string $currencyId, string $tx): string
    {
        $currency = Currency::find($currencyId);

        return $currency->blockchain->getExplorerUrl() . '/' . $tx;
    }
}
