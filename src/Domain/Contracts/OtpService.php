<?php

declare(strict_types=1);

namespace Skywalker\Otp\Domain\Contracts;

use Closure;

interface OtpService
{
    /**
     * Generate an OTP for the given identifier.
     */
    public function generate(string $identifier): string;

    /**
     * Verify the OTP for the given identifier.
     *
     * @throws \Skywalker\Otp\Exceptions\InvalidOtpException
     */
    public function verify(string $identifier, string $token): bool;

    /**
     * Set a custom OTP generator.
     *
     * @param  Closure(): string  $callback
     */
    public static function useGenerator(Closure $callback): void;

    /**
     * Generate a set of backup codes for the user.
     *
     * @return array<int, string>
     */
    public function generateBackupCodes(string $identifier, int $quantity = 8): array;

    /**
     * Verify and consume a backup code.
     */
    public function verifyBackupCode(string $identifier, string $code): bool;

    /**
     * Generate a Magic Login Link.
     */
    public function generateMagicLink(string $identifier): string;
}
