<?php

namespace Database\Seeders;

use App\Enums\HeartbeatServiceName;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Throwable;

class ServicesSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run()
    {
        $services = HeartbeatServiceName::cases();
        foreach ($services as $service) {
            switch ($service) {
                case HeartbeatServiceName::ServiceBinance:
                    $url = 'https://api.binance.com/api/v3/ticker/price';
                    break;

                case HeartbeatServiceName::ServiceCoinGate:
                    $url = 'https://api.coingate.com/v2/rates';
                    break;

                case HeartbeatServiceName::ServiceProcessing:
                    $url = config('processing.url') . '/status';
                    break;

                case HeartbeatServiceName::ServiceBitcoinExplorer:
                    $url = config('explorer.bitcoinExplorerUrl') . '/status';
                    break;

                case HeartbeatServiceName::ServiceTronExplorer:
                    $url = config('explorer.tronExplorerUrl') . '/status';
                    break;

                default:
                    $url = null;
            }

            if ($record = Service::where('slug', $service)->first()) {
                $record->name = $service->title();
                $record->slug = $service->value;
                $record->url = $url;
            } else {
                $record = new Service([
                    'name' => $service->title(),
                    'slug' => $service->value,
                    'url' => $url,
                ]);
            }

            $record->saveOrFail();
        }
    }
}