<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Surfnet\YubikeyApiClient\Crypto\RandomNonceGenerator;
use Surfnet\YubikeyApiClient\Crypto\Signer;
use Surfnet\YubikeyApiClient\Http\ServerPoolClient;
use Surfnet\YubikeyApiClient\Otp;
use Surfnet\YubikeyApiClient\Service\OtpVerificationResult;
use Surfnet\YubikeyApiClient\Service\VerificationService;

require __DIR__ . '/../vendor/autoload.php';

const YUBIKEY_CLIENT_ID     = '12345';
const YUBIKEY_CLIENT_SECRET = 'secret';

$service = new VerificationService(
    new ServerPoolClient(new GuzzleClient()),
    new RandomNonceGenerator(),
    new Signer(YUBIKEY_CLIENT_SECRET),
    YUBIKEY_CLIENT_ID
);

$userInputOtp = 'cchfgeetctchcgfhlvrhrhrrlilfeklvicidfeklgvlv';
if (!Otp::isValid($userInputOtp)) {
    // User-entered OTP string is not valid.
}

$otp = Otp::fromString($userInputOtp);
$result = $service->verify($otp);

if ($result->isSuccessful()) {
    // Yubico verified OTP.
} else {
    switch ($result->getError()) {
        case OtpVerificationResult::ERROR_REPLAYED_OTP:
            // ...
    }
}
