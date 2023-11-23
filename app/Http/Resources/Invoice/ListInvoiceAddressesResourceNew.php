<?php

declare(strict_types=1);

namespace App\Http\Resources\Invoice;

use App\Enums\Blockchain;
use App\Enums\InvoiceAddressStatus;
use App\Enums\InvoiceStatus;
use App\Helpers\DateTimeFormatter;
use App\Http\Resources\BaseResource;
use App\Models\InvoiceAddress;
use App\Services\PublicExplorer\PublicExplorerFactory;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * ListInvoiceAddressesResourceNew
 */
class ListInvoiceAddressesResourceNew extends BaseResource
{
    protected const SAT_IN_BTC = 100000000; // Satoshi in 1 Bitcoin (10^8)

    /**
     * @param $request
     * @return array
     * @throws Exception
     */
    public function toArray($request): array
    {
        $publicExplorer = PublicExplorerFactory::makeExplorer($this->currency_id);
        $status = $this->getStatus($this->address, $this->watch_id);

        return [
            'address' => $this->address,
            'balance' => (string)$this->balance,
            'balanceFromExplorer' => $this->getExplorerBalance($this->address, $this->blockchain),
            'balanceUsd' => (string)$this->balance_usd,
            'currencyId' => $this->currency_id,
            'state' => $this->state,
            'statusTitle' => $status['title'],
            'statusDescription' => $status['description'],
            'explorerUrl' => !empty($publicExplorer) ? $publicExplorer->getAddressUrl($this->address) : '',
        ];
    }

    /**
     * @param string $address
     * @param string|null $watchId
     * @return array
     */
    private function getStatus(string $address, ?string $watchId): array
    {
        if (!$watchId) {
            $status = [
                'title' => __(InvoiceAddressStatus::Ready->title()),
                'description' => __('Address is free.'),
            ];

            return $status;
        }

        $invoiceAddress = InvoiceAddress::where([
            ['address', $address],
            ['watch_id', $watchId],
        ])->first();

        if (!$invoiceAddress) {
            $status = [
                'title' => __(InvoiceAddressStatus::UsedInvoice->title()),
                'description' => __('Unlinked address.'),
            ];

            return $status;
        }

        $invoice = $invoiceAddress->invoice;

        if ($invoice->status == InvoiceStatus::Waiting) {
            $ago = DateTimeFormatter::getAgoTimeText($invoice->expired_at->format(DATE_ATOM));

            $status = [
                'title' => __(InvoiceAddressStatus::LinkedToInvoice->title()),
                'description' => __('Linked to invoice :invoiceId, waiting for payment :time.',
                    [
                        'invoiceId' => $invoice->id,
                        'time' => $ago,
                    ]
                ),
            ];

            return $status;
        }

        if ($invoice->status == InvoiceStatus::WaitingConfirmations) {
            $status = [
                'title' => __(InvoiceAddressStatus::LinkedToInvoice->title()),
                'description' => __('The payment has appeared in the blockchain and is awaiting confirmation.'),
            ];

            return $status;
        }

        if ($invoice->status == InvoiceStatus::Expired) {
            $status = [
                'title' => __(InvoiceAddressStatus::Hold->title()),
                'description' => __('Incorrect payment, incorrect amount, address drops out of rotation.'),
            ];

            return $status;
        }

        if ($invoice->status == InvoiceStatus::PartiallyPaidExpired) {
            $ago = $invoice->expired_at->format('U') - $invoice->created_at->format('U');

            $status = [
                'title' => __(InvoiceAddressStatus::Hold->title()),
                'description' => __('Awaiting overdue payment (did not meet the payment time or the user canceled the invoice).'),
            ];

            return $status;
        }

        $status = [
            'title' => __(InvoiceAddressStatus::Hold->title()),
            'description' => __('Incorrect payment, incorrect amount, address drops out of rotation.'),
        ];

        return $status;
    }

    /**
     * todo remove logic from resource
     * @param string $address
     * @param Blockchain $blockchain
     * @return string
     * @throws GuzzleException
     * @throws BindingResolutionException
     */
    private function getExplorerBalance(string $address, Blockchain $blockchain): string
    {
        $guzzle = app()->make(Client::class);

        $url = $blockchain->getWalletExplorerUrl() . $address;

        try {
            $response = $guzzle->get($url);
        } catch (BadResponseException $exception) {
            return "0";
        }

        $result = json_decode($response->getBody()->getContents());

        $balance = '0';
        if ($blockchain == Blockchain::Bitcoin) {
            $balance = $result->final_balance / static::SAT_IN_BTC;
        }

        if ($blockchain == Blockchain::Tron) {
            if (empty($result->withPriceTokens)) {
                return $balance;
            }

            foreach ($result->withPriceTokens as $token) {
                if ($token->tokenName == 'Tether USD') {
                    $balance = $token->balance / pow(10, $token->tokenDecimal);
                }
            }
        }

        return (string)$balance;
    }
}
