<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Illuminate\Support\Facades\Hash;
use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Exceptions\InvalidOtpException;
use Skywalker\Support\Actions\Action;

class VerifyOtp extends Action
{
    /**
     * @param mixed ...$args [OtpStore, string $identifier, string $token]
     * @return bool
     * @throws InvalidOtpException
     */
    public function execute(...$args): bool
    {
        $store      = $args[0] ?? throw new \InvalidArgumentException('OtpStore is required.');
        $identifier = $args[1] ?? throw new \InvalidArgumentException('Identifier is required.');
        $token      = $args[2] ?? throw new \InvalidArgumentException('Token is required.');

        assert($store instanceof OtpStore);
        assert(is_string($identifier));
        assert(is_string($token));

        $otpToken = $store->get($identifier);

        if ($otpToken && !$otpToken->isExpired() && Hash::check($token, $otpToken->hashedToken)) {
            $store->delete($identifier);
            return true;
        }

        throw new InvalidOtpException("Invalid or expired OTP.");
    }
}
