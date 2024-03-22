<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Service;

use Surfnet\YubikeyApiClient\Otp;

interface VerificationServiceInterface
{
    public function verify(Otp $otp): OtpVerificationResult;
}
