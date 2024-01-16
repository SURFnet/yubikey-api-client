<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Tests\Crypto;

use PHPUnit\Framework\TestCase;
use Surfnet\YubikeyApiClient\Crypto\RandomNonceGenerator;

class RandomNonceGeneratorTest extends TestCase
{
    public function testItGeneratesAHexNonceOfTheCorrectLength(): void
    {
        $generator = new RandomNonceGenerator;
        $nonce = $generator->generateNonce();

        $this->assertSame(1, preg_match('/^[a-f0-9]{40}$/', $nonce));
    }
}
