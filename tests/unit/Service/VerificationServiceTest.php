<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Tests\Service;

use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Surfnet\YubikeyApiClient\Crypto\Signer;
use Surfnet\YubikeyApiClient\Http\ServerPoolClient;
use Surfnet\YubikeyApiClient\Service\VerificationService;
use Surfnet\YubikeyApiClient\Tests\Crypto\NonceGeneratorStub;

class VerificationServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testVerifiesOtp(): void
    {
        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse($otpString, $nonce);
        $httpClient = $this->createHttpClient($expectedResponse);
        $nonceGenerator = new NonceGeneratorStub('surfnet');
        $signer = $this->createDummySigner($expectedQuery, true);

        $otp = m::mock('Surfnet\YubikeyApiClient\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($httpClient, $nonceGenerator, $signer, '1234');

        $this->assertTrue($service->verify($otp)->isSuccessful());
    }

    public function testVerifiesResponseOtpEqualsRequestOtp(): void
    {
        $this->expectException(
            'Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException',
            'OTP doesn\'t match'
        );

        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse('different OTP', $nonce);
        $httpClient = $this->createHttpClient($expectedResponse);
        $nonceGenerator = new NonceGeneratorStub('surfnet');
        $signer = $this->createDummySigner($expectedQuery, true);

        $otp = m::mock('Surfnet\YubikeyApiClient\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($httpClient, $nonceGenerator, $signer, '1234');
        $service->verify($otp);
    }

    public function testVerifiesResponseNonceEqualsRequestNonce(): void
    {
        $this->expectException(
            'Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException',
            'nonce doesn\'t match'
        );

        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse($otpString, 'different nonce');
        $httpClient = $this->createHttpClient($expectedResponse);
        $nonceGenerator = new NonceGeneratorStub('surfnet');
        $signer = $this->createDummySigner($expectedQuery, true);

        $otp = m::mock('Surfnet\YubikeyApiClient\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($httpClient, $nonceGenerator, $signer, '1234');
        $service->verify($otp);
    }

    public function testVerifiesServerSignature(): void
    {
        $this->expectException(
            'Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException',
            'signature doesn\'t match'
        );

        $otpString = 'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv';
        $nonce = 'surfnet';
        $expectedQuery = [
            'id' => '1234',
            'otp' => $otpString,
            'nonce' => $nonce,
        ];

        $expectedResponse = $this->createVerificationResponse($otpString, $nonce);
        $httpClient = $this->createHttpClient($expectedResponse);
        $nonceGenerator = new NonceGeneratorStub('surfnet');
        $signer = $this->createDummySigner($expectedQuery, false);

        $otp = m::mock('Surfnet\YubikeyApiClient\Otp');
        $otp->otp = $otpString;

        $service = new VerificationService($httpClient, $nonceGenerator, $signer, '1234');
        $service->verify($otp);
    }

    /**
     * @param string $otpString
     * @param string $nonce
     * @return ResponseInterface
     */
    private function createVerificationResponse(string $otpString, string $nonce): ResponseInterface
    {
        $expectedResponse = m::mock('Psr\Http\Message\ResponseInterface')
            ->shouldReceive('getBody')->once()->andReturn("status=OK\r\notp=$otpString\r\nnonce=$nonce")
            ->getMock();

        return $expectedResponse;
    }

    /**
     * @param ResponseInterface $expectedResponse
     * @return ServerPoolClient
     */
    private function createHttpClient(ResponseInterface $expectedResponse): ServerPoolClient
    {
        $httpClient = m::mock('Surfnet\YubikeyApiClient\Http\ServerPoolClient')
            ->shouldReceive('get')->once()->andReturn($expectedResponse)
            ->getMock();

        return $httpClient;
    }

    /**
     * @param array $request
     * @param boolean $verifiesSignature
     * @return Signer
     */
    private function createDummySigner(array $request, bool $verifiesSignature): Signer
    {
        $signer = m::mock('Surfnet\YubikeyApiClient\Crypto\Signer')
            ->shouldReceive('sign')->once()->with($request)->andReturn($request)
            ->shouldReceive('verifySignature')->once()->andReturn($verifiesSignature)
            ->getMock();

        return $signer;
    }
}
