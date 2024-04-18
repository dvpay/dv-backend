<?php

namespace App\Services\Processing\Fake;

use App\Services\Processing\Contracts\HeartbeatContract;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class HeartbeatFake implements HeartbeatContract
{

    /**
     * @throws GuzzleException
     */
    public function getStatusService(): ResponseInterface
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(
                [
                    "sentAt"     => [
                        "bitcoin" => 1702008774,
                        "tron"    => 1702018722
                    ],
                    "receivedAt" => [
                        "bitcoin" => 1701957732,
                        "tron"    => 1702017330
                    ],
                    "appVersion" => [
                        "release"    => "1.0.1",
                        "commitHash" => "bb821323"
                    ],
                    "network"    => "mainnet",
                    "nodeStatus" => [
                        "bitcoin" => [
                            "network"        => "",
                            "explorerHeight" => 838862,
                            "nodeHeight"     => 838862,
                            "lastBlockAt"    => 1702018430,
                            "success"        => true,
                            "version"        => "250000",
                            "appVersion"     => [
                                "release"    => "23.10.26.1",
                                "commitHash" => "be28165a"
                            ]
                        ],
                        "tron"    => [
                            "network"        => "mainnet",
                            "explorerHeight" => 57128574,
                            "nodeHeight"     => 57128575,
                            "lastBlockAt"    => 1702018722,
                            "success"        => true,
                            "version"        => "4.7.3",
                            "appVersion"     => [
                                "release"    => "09.11.10.1",
                                "commitHash" => "1f4ee81d"
                            ]
                        ]
                    ],
                    "success"    => true
                ]
            )),
            new Response(202, ['Content-Length' => 0]),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        return $client->request('GET', '/');
    }
}