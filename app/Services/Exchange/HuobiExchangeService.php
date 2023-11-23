<?php

namespace App\Services\Exchange;

use App\Dto\Exchange\DepositAddressDto;
use App\Enums\ExchangeAddressType;
use App\Enums\ExchangeChainType;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Enums\HttpMethod;
use App\Exceptions\ApiException;
use App\Models\Currency;
use App\Models\ExchangeColdWalletWithdrawal;
use App\Models\ExchangeUserPairs;
use App\Models\ExchangeWithdrawalWallet;
use GuzzleHttp\Promise\PromiseInterface;
use Http\Client\Exception\RequestException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class HuobiExchangeService extends AbstractExchange
{
    private ExchangeServiceEnum $serviceName = ExchangeServiceEnum::Huobi;

    private string $accessKey;
    private string $secretKey;
    protected string $nonce = '';
    protected array $data = [];
    protected string $type = '';
    protected string $path = '';
    protected string $signature = '';
    protected array $options = [];

    public function __construct(private readonly PendingRequest $client)
    {
        $this->client->withHeaders([
            'Content-Type' => 'application/json',
        ]);
    }

    public function getExchangeName(): string
    {
        return 'Huobi';
    }

    public function loadDepositAddress(): array
    {
        return $this->loadAddresses(ExchangeAddressType::Deposit);
    }

    public function loadWithdrawalAddress(): array
    {
        return $this->loadAddresses(ExchangeAddressType::Withdraw);
    }

    private function loadAddresses(ExchangeAddressType $addressType): array
    {
        $currencies = Currency::where('has_balance', true)->get();
        $addresses = [];

        foreach ($currencies as $currency) {
            $exchangeAddress = $this->getAddress($addressType, $currency->name->value);
            $exchangeAddress = $this->filterAddress($exchangeAddress);
            if (empty($exchangeAddress)) continue;
            foreach ($exchangeAddress as $address) {
                $dto = new DepositAddressDto([
                    'currency'       => $address->currency,
                    'exchangeUserId' => $address->userId ?? null,
                    'address'        => $address->address,
                    'chain'          => $address->chain,
                    'type'           => $addressType,
                ]);
                $this->saveExchangeAddress($dto, $this->accessKey, $addressType);
            }

            $addresses = array_merge($addresses, $exchangeAddress);
        }

        return $addresses;
    }

    public function loadSymbolsByCurrency(): Collection
    {
        return Cache::remember('symbols_by_currency', 3600, function () {
            $currencies = Currency::where('has_balance', true)
                ->get()
                ->pluck('code')
                ->map(fn($item) => strtolower($item->value));

            $symbols = collect($this->getSymbols());

            return $symbols->filter(fn($item) => $currencies->contains($item->{'base-currency'}))
                ->map(function ($item) {
                    return [
                        'fromCurrencyId' => $item->{'base-currency'},
                        'toCurrencyId'   => $item->{'quote-currency'},
                        'symbol'         => $item->symbol
                    ];
                })
                ->groupBy('fromCurrencyId');
        });
    }

    public function loadExchangeSymbols(): Collection
    {
        return Cache::remember('exchange_by_currency', 3600, function (): Collection {
            $list = collect($this->getSymbolsV2());
            $sellSymbols = $list->map(function ($item) {
                return (object)[
                    'currencyFrom' => $item->bc,
                    'currencyTo'   => $item->qc,
                    'label'        => $item->bc . '/' . $item->qc,
                    'symbol'       => $item->symbol,
                    'type'         => 'sell'
                ];
            });

            $buySymbols = $list->map(function ($item) {
                return (object)[
                    'currencyFrom' => $item->qc,
                    'currencyTo'   => $item->bc,
                    'label'        => $item->qc . '/' . $item->bc,
                    'symbol'       => $item->symbol,
                    'type'         => 'buy'
                ];
            });

            return $sellSymbols->merge($buySymbols);
        });
    }

    /**
     * @throws \Throwable
     */
    public function exchange(ExchangeUserPairs $exchangeUserPairs): ?object
    {
        $accounts = collect($this->getAccounts());

        $spotAccount = $accounts->where('state', '=', 'working')
            ->where('type', '=', 'spot')
            ->first();

        if (!$spotAccount) {
            Log::channel('exchangeLog')->error('No working spot account found');
            return null;
        }

        $balances = collect($this->getBalance(data: [], accountID: $spotAccount->id)->list);

        $balanceTicker = $balances->where('currency', $exchangeUserPairs->currency_from)
            ->where('type', '=', 'trade')
            ->first();

        if (!$balanceTicker) {
            Log::channel('exchangeLog')->error('Balance ticker not found');
            return null;
        }


        Log::channel('exchangeLog')->error('ExchangeWithdrawal (balances)', [$balanceTicker]);

        $amount = floor((float)$balanceTicker->balance * 10000) / 10000;

        $result = $this->placeOrder([
            'account-id' => $spotAccount->id,
            'symbol'     => $exchangeUserPairs->symbol,
            'type'       => $exchangeUserPairs->type . '-market',
            'amount'     => $amount,
            'source'     => 'api',
        ]);

        Log::channel('exchangeLog')->error('ExchangeWithdrawal (placeOrder)', [$result]);

        if ($result->status != 'ok') {
            Log::channel('exchangeLog')->error('ExchangeWithdrawal (error)', [$result]);
            return null;
        }

        return $result;
    }

    /**
     * @throws \Throwable
     */
    public function withdrawalFromExchange(): void
    {
        $accounts = collect($this->getAccounts());

        $spotAccount = $accounts->where('state', '=', 'working')
            ->where('type', '=', 'spot')
            ->first();

        if (!$spotAccount) {
            Log::channel('exchangeLog')->error('No working spot account found for user:' . $this->user->id);
            return;
        }

        $wallets = ExchangeWithdrawalWallet::select()
            ->selectRaw('ROW_NUMBER() OVER (PARTITION BY currency ORDER BY RAND()) AS rn')
            ->where('user_id', $this->user->id)
            ->where('exchange_id', $this->serviceName->getId())
            ->where('is_withdrawal_enable', true)
            ->get()
            ->where('rn', 1);

        $balances = collect($this->getBalance(data: [], accountID: $spotAccount->id)->list);

        foreach ($wallets as $wallet) {
            $balanceTicker = $balances->where('balance', '>', 0)
                ->where('type', '=', 'trade')
                ->where('currency', '=', $wallet->currency)
                ->first();

            if ($balanceTicker->balance < $wallet->min_balance) {
                continue;
            };

            $amount = floor((float)$balanceTicker->balance - 10);

            $result = $this->placeWithdrawal([
                'address'  => $wallet->address,
                'amount'   => $amount,
                'currency' => $wallet->currency,
                'chain'    => $wallet->chain,
            ]);

            if (empty($result->status) || $result->status != 'ok') {
                Log::channel('exchangeLog')->error('Withdrawal failed', [$result]);
                continue;
            }

            Log::channel('exchangeLog')->info('Withdrawal completed', [$result]);

            $exchangeColdWalletWithdrawal = new ExchangeColdWalletWithdrawal([
                'exchange_cold_wallet_id' => $wallet->id,
                'exchange_id' => ExchangeServiceEnum::Huobi->getId(),
                'address' => (string)$wallet->address,
                'amount' => (string)$amount,
            ]);
            $exchangeColdWalletWithdrawal->saveOrFail();
        };

    }

    public function calculateUsdt(string $symbol, float $amount)
    {
        $detail = $this->getMarketDetail(['symbol' => $symbol]);
        return $amount * $detail->tick->open;
    }

    public function calculateToken(string $symbol, float $amount)
    {
        $detail = $this->getMarketDetail(['symbol' => $symbol]);
        return $amount / $detail->tick->open;
    }

    public function setKeys(): void
    {
        $keys = $this->getKeys($this->serviceName);
        $this->accessKey = $keys['accessKey'];
        $this->secretKey = $keys['secretKey'];
    }

    protected function auth(): void
    {
        $this->nonce();
        $this->signature();
    }

    protected function signature(): void
    {
        if (empty($this->accessKey)) return;

        $param = [
            'AccessKeyId'      => $this->accessKey,
            'SignatureMethod'  => 'HmacSHA256',
            'SignatureVersion' => 2,
            'Timestamp'        => $this->nonce,
        ];

        $param = array_merge($param, $this->data);
        $param = $this->sort($param);

        $host_tmp = explode('https://', $this->config['apiUrl']);
        if (isset($host_tmp[1])) $temp = $this->type . "\n" . $host_tmp[1] . "\n" . $this->path . "\n" . implode('&', $param);
        $signature = base64_encode(hash_hmac('sha256', $temp ?? '', $this->secretKey, true));

        $param[] = "Signature=" . urlencode($signature);

        $this->signature = implode('&', $param);
    }


    protected function sort($param): array
    {
        $u = [];
        foreach ($param as $k => $v) {
            if (is_array($v)) $v = json_encode($v);
            $u[] = $k . "=" . urlencode($v);
        }
        asort($u);

        return $u;
    }

    protected function nonce(): void
    {
        $this->client->baseUrl($this->config['apiUrl']);
        $this->nonce = date('Y-m-d\TH:i:s', time());
    }


    /**
     * @throws \Exception
     */
    protected function exec(): PromiseInterface|Response
    {
        $this->auth();

        if (!empty($this->data) && $this->type != 'GET') {
            $this->options['body'] = json_encode($this->data);
        }
        if ($this->type == 'GET' && empty($this->accessKey)) {
            $this->signature = empty($this->data) ? '' : http_build_query($this->data);
        }

        try {
            return $this->client->send($this->type, $this->path . '?' . $this->signature, $this->options);
        } catch (RequestException $exception) {
            throw new ApiException(__('Huobi Api exeption') . $exception->getMessage(), 400);
        }
        return $response;
    }

    private function filterAddress($addresses): array
    {
        return array_filter($addresses, fn($item) => in_array($item->chain, ExchangeChainType::values()));
    }

    private function getAddress(ExchangeAddressType $addressType, string $currencyName): array
    {
        $methodName = ($addressType === ExchangeAddressType::Deposit) ? 'getDepositAddress' : 'getWithdrawalAddress';
        return $this->$methodName(['currency' => strtolower($currencyName)]);
    }

    /**
     * @throws \Exception
     */
    protected function getDepositAddress(array $data)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/v2/account/deposit/address';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    protected function getWithdrawalAddress(array $data)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/v2/account/withdraw/address';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    protected function getSymbols(array $data = [])
    {
        $this->type = HttpMethod::GET->value;
        $this->path = 'v1/common/symbols';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    protected function getSymbolsV2(array $data = [])
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/v1/settings/common/market-symbols';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    protected function getMarketDetail(array $data)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/market/detail';
        $this->data = $data;
        return $this->exec()->object();
    }

    /**
     * @throws \Exception
     */
    protected function getBalance(array $data, $accountID)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = "/v1/account/accounts/{$accountID}/balance";
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    protected function getAccounts(array $data = [])
    {
        $this->type = HttpMethod::GET->value;
        $this->path = "/v1/account/accounts";
        $this->data = $data;
        return $this->exec()->object()?->data;
    }

    protected function placeOrder(array $data = [])
    {
        $this->type = HttpMethod::POST->value;
        $this->path = "/v1/order/orders/place";
        $this->data = $data;
        return $this->exec()->object();
    }

    protected function placeWithdrawal(array $data)
    {
        $this->type = HttpMethod::POST->value;
        $this->path = "/v1/dw/withdraw/api/create";
        $this->data = $data;
        return $this->exec()->object();
    }
}
