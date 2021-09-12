<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Tests;

use Surfnet\YubikeyApiClient\Otp;
use TypeError;

class OtpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider otpStrings
     * @param string $string
     * @param string $otpString
     * @param string $password
     * @param string $publicId
     * @param string $cipherText
     */
    public function testItParsesFromString(string $string, string $otpString, string $password, string $publicId, string $cipherText)
    {
        $otp = Otp::fromString($string);

        $this->assertSame($otpString, $otp->otp);
        $this->assertSame($password, $otp->password);
        $this->assertSame($publicId, $otp->publicId);
        $this->assertSame($cipherText, $otp->cipherText);
    }

    /**
     * @dataProvider otpStrings
     * @param string $string
     */
    public function testItValidatesCorrectOtps(string $string): void
    {
        $this->assertTrue(Otp::isValid($string));
    }

    public function otpStrings(): array
    {
        return [
            'Regular OTP' => [
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'ddddddbtbhnh',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Password OTP' => [
                'passwd:ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                'passwd',
                'ddddddbtbhnh',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Short public id' => [
                'vvvvvcjnkcfeiegrrnnednjcluulduerelthv',
                'vvvvvcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'vvvvv',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Long public id' => [
                'ccccddddeeeeffffcjnkcfeiegrrnnednjcluulduerelthv',
                'ccccddddeeeeffffcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'ccccddddeeeeffff',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
            'Dvorak OTP' => [
                'jxe.uidchtnbpygkjxe.uidchtnbpygkjxe.uidchtnbpygk',
                'cbdefghijklnrtuvcbdefghijklnrtuvcbdefghijklnrtuv',
                '',
                'cbdefghijklnrtuv',
                'cbdefghijklnrtuvcbdefghijklnrtuv'
            ],
            'Dvorak OTP w/ password' => [
                'passwd:jxe.uidchtnbpygkjxe.uidchtnbpygkjxe.uidchtnbpygk',
                'cbdefghijklnrtuvcbdefghijklnrtuvcbdefghijklnrtuv',
                'passwd',
                'cbdefghijklnrtuv',
                'cbdefghijklnrtuvcbdefghijklnrtuv'
            ],
            'Mixed case OTP is lowercased' => [
                'ddddddbTBHNHCJNKCFEIEGRRnnednjclUULDUerelthv',
                'ddddddbtbhnhcjnkcfeiegrrnnednjcluulduerelthv',
                '',
                'ddddddbtbhnh',
                'cjnkcfeiegrrnnednjcluulduerelthv'
            ],
        ];
    }

    /**
     * @dataProvider nonOtpStrings
     * @param mixed $nonOtpString
     */
    public function testItThrowsAnExceptionWhenGivenStringIsNotAnOtpString(string $nonOtpString): void
    {
        $this->expectException('Surfnet\YubikeyApiClient\Exception\InvalidArgumentException', 'not a valid OTP');

        \Surfnet\YubikeyApiClient\Otp::fromString($nonOtpString);
    }

    /**
     * @dataProvider nonOtpStrings
     * @param string $string
     */
    public function testItDoesntAcceptInvalidOtps(string $string): void
    {
        $this->assertFalse(Otp::isValid($string));
    }

    public function nonOtpStrings(): array
    {
        return [
            'Has invalid characters' => ['abcdefghijklmnopqrstuvwxyz123456789'],
            'Too long' => [str_repeat('c', 100)],
            'Too short' => [str_repeat('c', 31)],
        ];
    }
}
