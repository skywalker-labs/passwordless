<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Illuminate\Support\Facades\Hash;
use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Exceptions\InvalidOtpException;
use Skywalker\Support\Foundation\Action;

class VerifyOtp extends Action
{
    protected OtpStore $store;

    public function __construct(
        ?OtpStore $store = null
    ) {
        $this->store = $store ?? app(OtpStore::class);
    }

    /**
     * @param  mixed  ...$args  [string $identifier, string $token]
     *
     * @throws InvalidOtpException
     */
    public function execute(...$args): bool
    {
        $identifier = $args[0] ?? throw new \InvalidArgumentException('Identifier is required.');
        $token = $args[1] ?? throw new \InvalidArgumentException('Token is required.');

        assert(is_string($identifier));
        assert(is_string($token));
        $otpToken = $this->store->get($identifier);

        if ($otpToken !== null && ! $otpToken->isExpired() && Hash::check($token, $otpToken->hashedToken)) {
            $this->store->delete($identifier);

            return true;
        }

        throw new InvalidOtpException('Invalid or expired OTP.');
    }
}
