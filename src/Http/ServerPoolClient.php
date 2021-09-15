<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class ServerPoolClient
{
    private static $serverPool = [
        'https://api.yubico.com/wsapi/2.0/verify',
        'https://api2.yubico.com/wsapi/2.0/verify',
        'https://api3.yubico.com/wsapi/2.0/verify',
        'https://api4.yubico.com/wsapi/2.0/verify',
        'https://api5.yubico.com/wsapi/2.0/verify',
    ];

    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @param Client $guzzleClient
     */
    public function __construct(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param array $requestOptions
     * @return ResponseInterface
     */
    public function get(array $requestOptions): ResponseInterface
    {
        $poolIndex = array_rand(self::$serverPool);
        try {
            return $this->guzzleClient->get(self::$serverPool[$poolIndex], $requestOptions);
        } catch (RequestException $e) {
            if ($e->getResponse()) {
                throw $e;
            }
        }

        // There is no server response (timeout, DNS failure); try again.
        $poolIndex = ($poolIndex + 1) % count(self::$serverPool);

        return $this->guzzleClient->get(self::$serverPool[$poolIndex], $requestOptions);
    }

    public function getServerPool(): array
    {
        return self::$serverPool;
    }
}
