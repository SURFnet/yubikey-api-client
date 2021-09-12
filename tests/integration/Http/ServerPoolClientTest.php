<?php

namespace Surfnet\YubikeyApiClient\IntegrationTest\Http;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase as UnitTest;
use Surfnet\YubikeyApiClient\Http\ServerPoolClient;

class ServerPoolClientTest extends UnitTest
{
    /**
     * @group nightly
     * @dataProvider differentServersInPoolProvider()
     * @param string $serverBaseUrl
     */
    public function testCanConnectAllServersInPool($serverBaseUrl)
    {
        $requestClient = new Client();

        // example request from https://developers.yubico.com/yubikey-val/Validation_Protocol_V2.0.html
        $url = $serverBaseUrl . '?otp=vvvvvvcucrlcietctckflvnncdgckubflugerlnr&id=87&timeout=8&sl=50'
               . '&nonce=askjdnkajsndjkasndkjsnad';

        $response = $requestClient->get($url);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('BAD_OTP', $response->getBody()->getContents());
    }

    /**
     * @return array
     */
    public function differentServersInPoolProvider()
    {
        $client = new ServerPoolClient(new Client());
        $servers = $client->getServerPool();

        $serversInPool = [];
        foreach ($servers as $index => $url) {
            $serversInPool['server no. ' . $index] = [$url];
        }

        return $serversInPool;
    }
}
