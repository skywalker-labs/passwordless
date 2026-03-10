<?php

declare(strict_types=1);

namespace Skywalker\Otp\Concerns;

use Skywalker\Otp\Domain\Contracts\OtpService as OtpServiceContract;
use Skywalker\Otp\Exceptions\InvalidOtpException;

/**
 * @property string $email
 * @property string|null $phone
 */
trait HasOtp
{
    /**
     * Send an OTP to the user.
     *
     * @return string The generated OTP.
     *
     * @throws \RuntimeException
     */
    public function sendOtp(): string
    {
        /** @var OtpServiceContract $service */
        $service = app(OtpServiceContract::class);

        // Determine identifier (email or phone)
        $identifier = $this->email ?? $this->phone;

        if (! $identifier) {
            throw new \RuntimeException('User must have an email or phone to receive OTP.');
        }

        return $service->generate($identifier);
    }

    /**
     * Verify an OTP for the user.
     *
     * @throws InvalidOtpException
     */
    public function verifyOtp(string $token): bool
    {
        /** @var OtpServiceContract $service */
        $service = app(OtpServiceContract::class);

        $identifier = $this->email ?? $this->phone;

        if (! $identifier) {
            throw new \RuntimeException('User must have an email or phone to verify OTP.');
        }

        return $service->verify($identifier, $token);
    }
}
