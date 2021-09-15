<?php

declare(strict_types=1);

namespace Surfnet\YubikeyApiClient\Service;

class OtpVerificationResult
{
    /** The OTP is valid. */
    const STATUS_OK = 'OK';
    /** The OTP is invalid format. */
    const ERROR_BAD_OTP = 'BAD_OTP';
    /** The OTP has already been seen by the service. */
    const ERROR_REPLAYED_OTP = 'REPLAYED_OTP';
    /** The HMAC signature verification failed. */
    const ERROR_BAD_SIGNATURE = 'BAD_SIGNATURE';
    /** The request lacks a parameter. */
    const ERROR_MISSING_PARAMETER = 'MISSING_PARAMETER';
    /** The request id does not exist. */
    const ERROR_NO_SUCH_CLIENT = 'NO_SUCH_CLIENT';
    /** The request id is not allowed to verify OTPs. */
    const ERROR_OPERATION_NOT_ALLOWED = 'OPERATION_NOT_ALLOWED';
    /** Unexpected error in our server. Please contact us if you see this error. */
    const ERROR_BACKEND_ERROR = 'BACKEND_ERROR';
    /** Server could not get requested number of syncs during before timeout */
    const ERROR_NOT_ENOUGH_ANSWERS = 'NOT_ENOUGH_ANSWERS';
    /** Server has seen the OTP/Nonce combination before */
    const ERROR_REPLAYED_REQUEST = 'REPLAYED_REQUEST';

    /**
     * @var string $status
     */
    private $status;

    /**
     * @param string $status
     */
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_OK;
    }

    /**
     * @return string|null NULL if verification was successful, or one of the ERROR_* constants.
     */
    public function getError(): ?string
    {
        return $this->isSuccessful() ? null : $this->status;
    }
}
