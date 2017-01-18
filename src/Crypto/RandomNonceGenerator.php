<?php

namespace Surfnet\YubikeyApiClient\Crypto;

class RandomNonceGenerator implements NonceGenerator
{
    public function generateNonce()
    {
        return bin2hex(random_bytes(32));
    }
}
