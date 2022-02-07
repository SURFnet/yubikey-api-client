<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class ServerPoolClient
{
    const YUBICO_API_VERIFY = 'https://api.yubico.com/wsapi/2.0/verify';

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
        return $this->guzzleClient->get(self::YUBICO_API_VERIFY, $requestOptions);
    }

    public function getServerPool(): array
    {
        return [self::YUBICO_API_VERIFY];
    }
}
