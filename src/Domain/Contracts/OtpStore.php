<?php

declare(strict_types=1);

namespace Skywalker\Otp\Domain\Contracts;

use Skywalker\Otp\Domain\ValueObjects\OtpToken;

interface OtpStore
{
    /**
     * Store the OTP token.
     */
    public function store(OtpToken $token): void;

    /**
     * Retrieve the OTP token for the given identifier.
     */
    public function get(string $identifier): ?OtpToken;

    /**
     * Delete the OTP token for the given identifier.
     */
    public function delete(string $identifier): void;
}
