<?php

/**
 * Copyright 2014 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Service;

use Surfnet\YubikeyApiClient\Crypto\NonceGenerator;
use Surfnet\YubikeyApiClient\Crypto\Signer;
use Surfnet\YubikeyApiClient\Exception\InvalidArgumentException;
use Surfnet\YubikeyApiClient\Exception\RequestResponseMismatchException;
use Surfnet\YubikeyApiClient\Exception\UntrustedSignatureException;
use Surfnet\YubikeyApiClient\Http\ServerPoolClient;
use Surfnet\YubikeyApiClient\Otp;

class VerificationService
{
    /**
     * @var ServerPoolClient
     */
    private ServerPoolClient $httpClient;

    /**
     * @var Signer
     */
    private Signer $signer;

    /**
     * @var string Yubico client ID
     */
    private string $clientId;

    /**
     * @var NonceGenerator
     */
    private NonceGenerator $nonceGenerator;

    /**
     * @param ServerPoolClient $httpClient
     * @param NonceGenerator $nonceGenerator
     * @param Signer $signer
     * @param string $clientId
     */
    public function __construct(
        ServerPoolClient $httpClient,
        NonceGenerator $nonceGenerator,
        Signer $signer,
        string $clientId
    ) {
        $this->httpClient = $httpClient;
        $this->signer = $signer;
        $this->clientId = $clientId;
        $this->nonceGenerator = $nonceGenerator;
    }

    /**
     * @param Otp $otp
     * @return OtpVerificationResult
     * @throws UntrustedSignatureException When the signature doesn't match the expected signature.
     * @throws RequestResponseMismatchException When the response data doesn't match the requested data (otp, nonce).
     */
    public function verify(Otp $otp): OtpVerificationResult
    {
        $nonce = $this->nonceGenerator->generateNonce();

        $query = [
            'id'    => $this->clientId,
            'otp'   => $otp->otp,
            'nonce' => $nonce,
        ];
        $query = $this->signer->sign($query);

        $httpResponse = $this->httpClient->get(['query' => $query]);
        $response = $this->parseYubicoResponse((string) $httpResponse->getBody());

        if (!$this->signer->verifySignature($response)) {
            throw new UntrustedSignatureException('The response data signature doesn\'t match the expected signature.');
        }

        if ($response['otp'] !== $otp->otp) {
            throw new RequestResponseMismatchException('The response OTP doesn\'t match the requested OTP.');
        }

        if ($response['nonce'] !== $nonce) {
            throw new RequestResponseMismatchException('The response nonce doesn\'t match the requested nonce.');
        }

        return new OtpVerificationResult($response['status']);
    }

    /**
     * Parses the response.
     *
     * @param string $response
     * @return array
     */
    private function parseYubicoResponse(string $response): array
    {
        $lines = array_filter(explode("\r\n", $response));
        $responseArray = [];

        foreach ($lines as $line) {
            list($key, $value) = explode('=', $line, 2);

            $responseArray[$key] = $value;
        }

        return $responseArray;
    }
}
