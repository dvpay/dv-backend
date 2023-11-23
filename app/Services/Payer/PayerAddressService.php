<?php

namespace App\Services\Payer;

use App\Enums\Blockchain;
use App\Models\Currency;
use App\Models\Notification\NotificationLog;
use App\Models\Payer;
use App\Models\PayerAddress;
use App\Models\Store;
use App\Notifications\PayerAddressNotification;
use App\Services\Processing\Contracts\AddressContract;
use Illuminate\Support\Facades\Notification;

final readonly class PayerAddressService
{
    public function __construct(
            private AddressContract $contract,
    ) {
    }

    public function address(Payer $payer, Currency $currency, Store $store): PayerAddress
    {
        $address = $this->contract->getStaticAddress($currency, $payer, $store->user->processing_owner_id);

        return PayerAddress::firstOrCreate([
                'payer_id'    => $payer->id,
                'currency_id' => $currency->id,
                'blockchain'  => $currency->blockchain,
                'address'     => $address['address'],
        ]);
    }

    public function getAllStaticAddressesForUser(Payer $payer, Store $store): array
    {
        $currencies = Currency::whereIn('blockchain', Blockchain::cases())
            ->where('has_balance', true)
            ->get();

        $payerAddresses = [];

        foreach ($currencies as $currency) {
            $address = $this->contract->getStaticAddress($currency, $payer, $store->user->processing_owner_id);

            $payerAddress = PayerAddress::firstOrCreate([
                'payer_id'    => $payer->id,
                'currency_id' => $currency->id,
                'blockchain'  => $currency->blockchain,
                'address'     => $address['address'],
            ]);

            $payerAddresses[] = $payerAddress;
        }

        return $payerAddresses;
    }

    public function emailNotification(string $email, array $payerAddress, ?string $ip): void
    {
        $log = NotificationLog::where('email', $email)
            ->where('model_type', 'App\Notifications\PayerAddressNotification')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();

        if (!$log) {
            Notification::route('mail', $email)
                ->notify(new PayerAddressNotification($payerAddress, $ip));
        }
    }

    public function getPayerByAddress(string $address): PayerAddress
    {
        return PayerAddress::where('address', $address)
            ->with('payer.store')
            ->with('transactions')
            ->firstOrFail();
    }

}
