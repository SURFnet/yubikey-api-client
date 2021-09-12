<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Crypto;

class RandomNonceGenerator implements NonceGenerator
{
    public function generateNonce(): string
    {
        // Nonces can only be 16 to 40 character long according to the yubico specification
        return bin2hex(random_bytes(20));
    }
}
