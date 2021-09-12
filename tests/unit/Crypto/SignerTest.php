<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Tests\Crypto;

use Surfnet\YubikeyApiClient\Crypto\Signer;

class SignerTest extends \PHPUnit\Framework\TestCase
{
    public function testItSignsData(): void
    {
        $signer = new Signer(base64_encode('surfnet'));
        $signedData = $signer->sign(['otp' => '1234']);

        $this->assertSame(['otp' => '1234', 'h' => 'AxRja+fRxnocSbsXKz0LXEOBCjw='], $signedData);
    }

    public function testItVerifiesSignature(): void
    {
        $signer = new Signer(base64_encode('surfnet'));
        $signedData = ['otp' => '1234', 'h' => 'AxRja+fRxnocSbsXKz0LXEOBCjw='];

        $this->assertTrue($signer->verifySignature($signedData));
    }

    public function testSignatureVerficationIgnoresUnknownResponseParams(): void
    {
        $signer = new Signer(base64_encode('surfnet'));
        $signedData = ['otp' => '1234', 'UNKNOWN' => 'PARAM', 'h' => 'AxRja+fRxnocSbsXKz0LXEOBCjw='];

        $this->assertTrue($signer->verifySignature($signedData));
    }

    /**
     * @dataProvider nonBase64DecodableStrings
     * @param mixed $nonBase64DecodableString
     */
    public function testClientSecretMustBeBase64DecodableString(string $nonBase64DecodableString): void
    {
        $this->expectException('Surfnet\YubikeyApiClient\Exception\InvalidArgumentException');

        new Signer($nonBase64DecodableString);
    }

    public function nonBase64DecodableStrings(): array
    {
        return [
            ['W*()$&#*($&)'],
            ['P:}{<>?>,'],
            ['   d89d'],
        ];
    }
}
