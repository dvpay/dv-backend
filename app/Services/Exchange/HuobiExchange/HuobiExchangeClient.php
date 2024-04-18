<?php

namespace App\Services\Exchange\HuobiExchange;

use App\Enums\HttpMethod;
use App\Exceptions\ApiException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class HuobiExchangeClient
{

    private string $accessKey = '';
    private string $secretKey = '';
    protected string $nonce = '';
    protected array $data = [];
    protected string $path = '';
    protected string $type = '';
    protected string $signature = '';

    public function __construct(
        protected PendingRequest $client,
        protected array $config,
        protected array $options = [],
    )
    {
        $this->client->withHeaders([
            'Content-Type' => 'application/json',
        ]);
    }

    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    public function setAccessKey(string $accessKey): void
    {
        $this->accessKey = $accessKey;
    }

    /**
     * @throws \Exception
     */
    public function getDepositAddress(array $data)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/v2/account/deposit/address';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    public function getWithdrawalAddress(array $data)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/v2/account/withdraw/address';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    public function getSymbols(array $data = [])
    {
        $this->type = HttpMethod::GET->value;
        $this->path = 'v1/common/symbols';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    public function getSymbolsV2(array $data = [])
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/v1/settings/common/market-symbols';
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    /**
     * @throws \Exception
     */
    public function getMarketDetail(array $data)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = '/market/detail';
        $this->data = $data;
        return $this->exec()->object();
    }

    /**
     * @throws \Exception
     */
    public function getBalance(array $data, $accountID)
    {
        $this->type = HttpMethod::GET->value;
        $this->path = "/v1/account/accounts/{$accountID}/balance";
        $this->data = $data;
        return $this->exec()->object()->data;
    }

    public function getAccounts(array $data = [])
    {
        $this->type = HttpMethod::GET->value;
        $this->path = "/v1/account/accounts";
        $this->data = $data;
        return $this->exec()->object()?->data;
    }

    public function placeOrder(array $data = [])
    {
        $this->type = HttpMethod::POST->value;
        $this->path = "/v1/order/orders/place";
        $this->data = $data;

        Log::channel('exchangeLog')->error('ExchangeWithdrawal (placeOrder) Request data: ' . json_encode($data));

        return $this->exec()->object();
    }

    public function placeWithdrawal(array $data)
    {
        $this->type = HttpMethod::POST->value;
        $this->path = "/v1/dw/withdraw/api/create";
        $this->data = $data;
        return $this->exec()->object();
    }

    protected function nonce(): void
    {
        $this->client->baseUrl($this->config['apiUrl']);
        $this->nonce = date('Y-m-d\TH:i:s', time());
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


    public function getAccessKey(): string
    {
        return $this->accessKey;
    }
}
