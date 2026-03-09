<?php

declare(strict_types=1);

namespace Skywalker\Otp\Domain\Contracts;

use Closure;

interface OtpService
{
    /**
     * Generate an OTP for the given identifier.
     *
     * @param string $identifier
     * @return string
     */
    public function generate(string $identifier): string;

    /**
     * Verify the OTP for the given identifier.
     *
     * @param string $identifier
     * @param string $token
     * @return bool
     * @throws \Skywalker\Otp\Exceptions\InvalidOtpException
     */
    public function verify(string $identifier, string $token): bool;

    /**
     * Set a custom OTP generator.
     *
     * @param Closure(): string $callback
     * @return void
     */
    public static function useGenerator(Closure $callback): void;

    /**
     * Generate a set of backup codes for the user.
     *
     * @param string $identifier
     * @param int $quantity
     * @return array<int, string>
     */
    public function generateBackupCodes(string $identifier, int $quantity = 8): array;

    /**
     * Verify and consume a backup code.
     *
     * @param string $identifier
     * @param string $code
     * @return bool
     */
    public function verifyBackupCode(string $identifier, string $code): bool;

    /**
     * Generate a Magic Login Link.
     *
     * @param string $identifier
     * @return string
     */
    public function generateMagicLink(string $identifier): string;
}
