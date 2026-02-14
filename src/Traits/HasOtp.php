<?php

namespace Skywalker\Otp\Traits;

use Skywalker\Otp\Services\OtpService;
use Illuminate\Support\Facades\App;

trait HasOtp
{
    /**
     * Send an OTP to the user.
     *
     * @return string
     */
    public function sendOtp()
    {
        $service = app('otp');
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
     */
    public function verifyOtp(string $token)
    {
        $service = app('otp');
        $identifier = $this->email ?? $this->phone;

        return $service->verify($identifier, $token);
    }
}
