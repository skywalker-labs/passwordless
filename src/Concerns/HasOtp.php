<?php

declare(strict_types=1);

namespace Skywalker\Otp\Concerns;

use Skywalker\Otp\Domain\Contracts\OtpService as OtpServiceContract;
use Illuminate\Support\Facades\App;

/**
 * @property string $email
 * @property string|null $phone
 */
trait HasOtp
{
    /**
     * Send an OTP to the user.
     *
     * @return string
     * @throws \Exception
     */
    public function sendOtp()
    {
        /** @var OtpServiceContract $service */
        $service = app(OtpServiceContract::class);
        
        // Determine identifier (email or phone)
        $identifier = $this->email ?? $this->phone; 
        
        if (!$identifier) {
             throw new \Exception("User must have an email or phone to receive OTP.");
        }

        return $service->generate($identifier);
    }

    /**
     * Verify an OTP for the user.
     *
     * @param string $token
     * @return bool
     * @throws \Skywalker\Otp\Exceptions\InvalidOtpException
     */
    public function verifyOtp(string $token)
    {
        /** @var OtpServiceContract $service */
        $service = app(OtpServiceContract::class);

        $identifier = $this->email ?? $this->phone;

        if (!$identifier) {
            throw new \Exception("User must have an email or phone to verify OTP.");
        }

        return $service->verify($identifier, $token);
    }
}
