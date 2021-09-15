<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Crypto;

interface NonceGenerator
{
    /**
     * @return string
     */
    public function generateNonce(): string;
}
