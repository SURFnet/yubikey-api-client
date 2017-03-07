<?php

namespace Surfnet\YubikeyApiClient\Tests\Http;

use GuzzleHttp\Exception\RequestException;
use Mockery as m;
use Surfnet\YubikeyApiClient\Http\ServerPoolClient;

class ServerPoolClientTest extends \PHPUnit_Framework_TestCase
{
    public function testItTriesOnce()
    {
        $guzzleClient = m::mock('GuzzleHttp\Client')
            ->shouldReceive('get')->once()->andReturn(
                m::mock('GuzzleHttp\Message\ResponseInterface')
            )
            ->getMock();

        $client = new ServerPoolClient($guzzleClient);

        $client->get([]);
    }

    public function testItTriesTwice()
    {
        $returnValues = [
            new RequestException('Comms failure', m::mock('Psr\Http\Message\RequestInterface'), /*No response*/ null),
            m::mock('GuzzleHttp\Message\ResponseInterface'),
        ];

        $client = new ServerPoolClient(
            m::mock('GuzzleHttp\Client')
                ->shouldReceive('get')->twice()->andReturnUsing(function () use (&$returnValues) {
                    return array_shift($returnValues);
                })
                ->getMock()
        );

        $client->get([]);
    }

    public function testItThrowsGuzzlesExceptionAfterTryingTwice()
    {
        $this->setExpectedException('GuzzleHttp\Exception\RequestException', 'Comms failure #2');

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

    public function testItThrowsGuzzlesExceptionWhenItHasAResponse()
    {
        $this->setExpectedException('GuzzleHttp\Exception\RequestException', 'Internal server error');

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
