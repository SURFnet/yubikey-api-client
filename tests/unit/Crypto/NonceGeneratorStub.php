<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Tests\Crypto;

use Surfnet\YubikeyApiClient\Crypto\NonceGenerator;

class NonceGeneratorStub implements NonceGenerator
{
    /**
     * @var string
     */
    private $nonce;

    /**
     * @param string $nonce
     */
    public function __construct(string $nonce)
    {
        $this->nonce = $nonce;
    }

    public function generateNonce(): string
    {
        return $this->nonce;
    }
}
