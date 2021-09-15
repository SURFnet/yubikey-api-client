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

    public function testItTriesTwice(): void
    {
        $returnValues = [
            new RequestException('Comms failure', m::mock('Psr\Http\Message\RequestInterface'), /*No response*/ null),
            m::mock('Psr\Http\Message\ResponseInterface'),
        ];

        $client = new ServerPoolClient(
            m::mock('GuzzleHttp\Client')
                ->shouldReceive('get')->twice()->andReturnUsing(function () use (&$returnValues) {
                    $r = array_shift($returnValues);
                    if ($r instanceof RequestException) {
                        throw $r;
                    }
                    return $r;
                })
                ->getMock()
        );

        $client->get([]);
    }

    public function testItThrowsGuzzlesExceptionAfterTryingTwice(): void
    {
        $this->expectException('GuzzleHttp\Exception\RequestException', 'Comms failure #2');

        $exceptions = [
            new RequestException('Comms failure #1', m::mock('Psr\Http\Message\RequestInterface'), null),
            new RequestException('Comms failure #2', m::mock('Psr\Http\Message\RequestInterface'), null),
        ];

        $client = new ServerPoolClient(
            m::mock('GuzzleHttp\Client')
                ->shouldReceive('get')->twice()->andReturnUsing(function () use (&$exceptions) {
                    throw array_shift($exceptions);
                })
                ->getMock()
        );

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
