<?php

declare(strict_types=1);

namespace Skywalker\Otp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string generate(string $identifier)
 * @method static bool verify(string $identifier, string $token)
 * @method static void useGenerator(\Closure $callback)
 * @method static string[] generateBackupCodes(string $identifier, int $quantity)
 * @method static bool verifyBackupCode(string $identifier, string $code)
 * @method static string generateMagicLink(string $identifier)
 *
 * @see \Skywalker\Otp\Domain\Contracts\OtpService
 */
class Otp extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'otp';
    }
}
