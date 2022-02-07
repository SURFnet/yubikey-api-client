<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Tests\Http;

use GuzzleHttp\Exception\RequestException;
use Mockery as m;
use Surfnet\YubikeyApiClient\Http\ServerPoolClient;

class ServerPoolClientTest extends \PHPUnit\Framework\TestCase
{
    public function testItTriesOnce(): void
    {
        $guzzleClient = m::mock('GuzzleHttp\Client')
            ->shouldReceive('get')->once()->andReturn(
                m::mock('Psr\Http\Message\ResponseInterface')
            )
            ->getMock();

        $client = new ServerPoolClient($guzzleClient);

        $client->get([]);
    }

    public function testItThrowsGuzzlesExceptionWhenItHasAResponse(): void
    {
        $this->expectException('GuzzleHttp\Exception\RequestException', 'Internal server error');

        $client = new ServerPoolClient(
            m::mock('GuzzleHttp\Client')
                ->shouldReceive('get')->twice()->andThrow(
                    new RequestException(
                        'Internal server error',
                        m::mock('Psr\Http\Message\RequestInterface'),
                        m::mock('Psr\Http\Message\ResponseInterface')
                            ->shouldReceive('getStatusCode')->andReturn(500)
                            ->getMock()
                    )
                )
                ->getMock()
        );

        $client->get([]);
    }
}
