<?php

namespace Surfnet\YubikeyApiClient\Crypto;

class RandomNonceGenerator implements NonceGenerator
{
    public function generateNonce()
    {
        // Nonces can only be 16 to 40 character long according to the yubico specification
        return bin2hex(random_bytes(20));
    }
}
