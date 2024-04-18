<?php

namespace Tests\Unit\WebhookServer;

 use App\Enums\InvoiceStatus;
 use App\WebhookServer\Signer\DefaultSigner;
 use Carbon\Carbon;
 use Tests\TestCase;

class SignerTest extends TestCase
{
    public function testItMakesCorrectDefaultSign()
    {
        $expectedSign = '4cbf4209985fa4e58e82f70db01eeae8b08f3ee363d3981c6c83d2d936e2ece8';
        $url = 'https:://example.com';
        $secret ='Expelliarmus';
        $payload = [
            'type' => 'payed',
            'orderId' => '',
            'status' => InvoiceStatus::Paid->value,
            'createdAt' => new Carbon('2024-01-01 00:00'),
            'paidAt' => new Carbon('2024-01-01 00:00'),
            'expiredAt' => new Carbon('2024-01-01 00:00'),
            'amount' => 666,
            'receivedAmount' => 666,
            'transactions' => [
                [
                    'txId' => '',
                    'createdAt' => '0c446b6f474696b5bea6bea9315229680b15d6f5',
                    'currency' => 'EthEthereum',
                    'blockchain' => 'Ethereum',
                    'amount' => 666,
                    'amountUsd' => 667,
                ]
            ],
            'payer' =>
                [
                    'id' => 1,
                    'storeUserId' => 'somestring'
                ]

        ];

        $signer = app(DefaultSigner::class);

        $sign = $signer->calculateSignature($url,$payload,$secret);

        $this->assertEquals($expectedSign,$sign);

    }
 }
