<?php

declare(strict_types=1);

namespace App\Services\Huobi;

use App\Enums\ExchangeService;
use App\Enums\HttpMethod;
use App\Models\Currency;
use App\Models\ExchangeRequest;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 *  HuobiService
 *  Documentation: https://huobiapi.github.io/docs/spot/v1/en/#quick-start
 */
#[Deprecated]
class HuobiService
{
    private string $apiUrl = 'https://api.huobi.pro';
    private string $route = '';
    private string $httpMethod = '';

    protected string $host = '';

	/**
	 * @param string $accessKey
	 * @param string $secretKey
	 * @param User   $user
	 */
    function __construct(
        public string $accessKey,
        public string $secretKey,
        public User $user
    )
    {
        $this->host = parse_url($this->apiUrl)['host'];
    }

    /**
     * @throws GuzzleException|Throwable
     */
    public function placeOrder(
        string $accountId,
        string $symbol,
        string $type,
        string $amount = '0.0001',
        string $price = ''
    )
    {
        $source = 'api';
        
        $this->route = "/v1/order/orders/place";
        
        $this->httpMethod = HttpMethod::POST->value;
        
        $postData = [
            'account-id' => $accountId,
            'symbol' => $symbol,
            'type' => $type,
            'amount' => $amount,
            'source' => $source,
        ];

        $url = $this->createSignUrl();
        
        return $this->request($url, $postData);
    }

	/**
	 * Create a Withdraw Request
	 * https://huobiapi.github.io/docs/spot/v1/en/#query-withdraw-address
	 *
	 * @param string $address
	 * @param string $amount
	 * @param string $currency
	 * @param float  $fee
	 * @param string $addrTag
	 * @param string $chain
	 *
	 * @return mixed
	 * @throws GuzzleException
	 * @throws Throwable
	 */
	public function createWithdrawal(string $address = '', string $amount = '0.00', string $currency = '', float $fee = 0, string $addrTag = '', string $chain = 'trc20usdt')
	{
		$this->route = '/v1/dw/withdraw/api/create';

		$this->httpMethod = HttpMethod::POST->value;

		$postData = [
			'address'  => $address,
			'amount'   => $amount,
			'currency' => $currency
		];

		if (!empty($fee) && $fee > 0) {
			$postData['fee'] = $fee;
		}
		if (!empty($chain)) {
			$postData['chain'] = $chain;
		}
		if (!empty($addrTag)) {
			$postData['addr-tag'] = $addrTag;
		}

		$url = $this->createSignUrl();

		//return (object)['status' => 'ok'];

		return $this->request($url, $postData);
	}

    /**
     * @throws GuzzleException|Throwable
     */
    function getBalance($accountId)
    {
        $this->route = "/v1/account/accounts/{$accountId}/balance";

        $this->httpMethod = HttpMethod::GET->value;

        $url = $this->createSignUrl();

        return $this->request($url);
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    public function getAccountAccounts()
    {
        $this->route = "/v1/account/accounts";

        $this->httpMethod = HttpMethod::GET->value;

        $url = $this->createSignUrl();

        return $this->request($url);
    }

	/**
	 * @return string
	 */
    private function createSignUrl($params = []): string
    {
        $param = [
            'AccessKeyId' => $this->accessKey,
            'SignatureMethod' => 'HmacSHA256',
            'SignatureVersion' => '2',
            'Timestamp' => date('Y-m-d\TH:i:s', time())
        ];

        $param = array_merge($param, $params);

        return $this->apiUrl . $this->route . '?' . $this->bindParam($param);
    }

	/**
	 * @param array $param
	 *
	 * @return string
	 */
    private function bindParam(array $param): string
    {
        $result = [];
        foreach ($param as $k => $v) {
            $result[] = $k . "=" . urlencode($v);
        }

        asort($result);

        $result[] = "Signature=" . urlencode($this->createSign($result));

        return implode('&', $result);
    }

	/**
	 * @param array $param
	 *
	 * @return string
	 */
    private function createSign(array $param): string
    {
        $signParam = $this->httpMethod . "\n" . $this->host . "\n" . $this->route . "\n" . implode('&', $param);

        $signature = hash_hmac('sha256', $signParam, $this->secretKey, true);

        return base64_encode($signature);
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    private function request(string $url, array $postData = [])
    {
        $guzzle = new Client(['verify' => false]);

        $args[RequestOptions::HEADERS] = [
            'Content-Type' => 'application/json',
        ];

        if ($postData != []) {
            $args[RequestOptions::JSON] = $postData;
        }

        $result = $guzzle->request(
            $this->httpMethod,
            $url,
            $args
        );

        $response = $result->getBody()->getContents();

        if ($result->getStatusCode() != Response::HTTP_OK) {
            throw new Exception('Huobi request - failed.');
        }

        ExchangeRequest::createLog(
            $this->user->id,
            ExchangeService::Huobi->getId(),
            json_encode([$url, $postData]),
            substr($response, 0, 35500)
        );

        return json_decode($response);
    }

	/**
	 * Converts merchant currencyId to Huobi currency symbol.
	 *
	 * Example: USDT.Tron -> usdt
	 * See: GET /v1/common/symbols
	 *
	 * @param string $currencyId
	 *
	 * @return string
	 */
	public static function getCurrencySymbolByCurrencyId(string $currencyId): string
	{
		$currency = explode('.', $currencyId);

		return strtolower($currency[0]);
	}

    public function getDepositAddresses()
    {
        $this->route = "/v2/account/deposit/address";

        $this->httpMethod = HttpMethod::GET->value;

        $currencies = Currency::where([
            ['has_balance', true],
        ])->get();

        $response = [];



        foreach ($currencies as $currency) {
            $url = $this->createSignUrl(['currency' => strtolower($currency->name->value)]);
            $response = array_merge($response, $this->request($url)->data);
        }

        return $response;
    }
}
