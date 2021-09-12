<?php

namespace Surfnet\YubikeyApiClient\Tests\Crypto;

use Surfnet\YubikeyApiClient\Crypto\RandomNonceGenerator;

class RandomNonceGeneratorTest extends \PHPUnit\Framework\TestCase
{
    public function testItGeneratesAHexNonceOfTheCorrectLength()
    {
        $generator = new RandomNonceGenerator;
        $nonce = $generator->generateNonce();

        $this->assertSame(1, preg_match('/^[a-f0-9]{40}$/', $nonce));
    }
}
