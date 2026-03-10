<?php

declare(strict_types=1);

namespace Skywalker\Otp\Services;

use Skywalker\Otp\Actions\GenerateBackupCodes;
use Skywalker\Otp\Actions\GenerateMagicLink;
use Skywalker\Otp\Actions\GenerateOtp;
use Skywalker\Otp\Actions\VerifyBackupCode;
use Skywalker\Otp\Actions\VerifyOtp;
use Skywalker\Otp\Domain\Contracts\OtpSender;
use Skywalker\Otp\Domain\Contracts\OtpService as OtpServiceContract;
use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Exceptions\InvalidOtpException;
use Skywalker\Support\Foundation\Service;

class OtpService extends Service implements OtpServiceContract
{
    protected int $length;

    protected int $expiry;

    protected string $channel;

    protected OtpStore $store;

    protected OtpSender $sender;

    /**
     * Custom OTP generator callback.
     *
     * @var \Closure|null
     */
    protected static $generator = null;

    public function __construct(OtpStore $store, OtpSender $sender)
    {
        $this->store = $store;
        $this->sender = $sender;

        $length = config('passwordless.length', 6);
        $this->length = is_int($length) ? $length : 6;

        $expiry = config('passwordless.expiry', 10);
        $this->expiry = is_int($expiry) ? $expiry : 10;

        $channel = config('passwordless.channel', 'mail');
        $this->channel = is_string($channel) ? $channel : 'mail';
    }

    public function generate(string $identifier): string
    {
        return (new GenerateOtp($this->store, $this->sender))->execute(
            $identifier,
            $this->length,
            $this->expiry,
            $this->channel,
            static::$generator
        );
    }

    /**
     * Set a custom OTP generator.
     *
     * @param  (\Closure(): string)  $callback
     */
    public static function useGenerator(\Closure $callback): void
    {
        static::$generator = $callback;
    }

    /**
     * Verify the OTP token.
     *
     * @throws InvalidOtpException
     */
    public function verify(string $identifier, string $token): bool
    {
        return (new VerifyOtp($this->store))->execute($identifier, $token);
    }

    /**
     * Generate a set of backup codes for the user.
     *
     * @return array<int, string>
     */
    public function generateBackupCodes(string $identifier, int $quantity = 8): array
    {
        return (new GenerateBackupCodes)->execute($identifier, $quantity);
    }

    /**
     * Verify and consume a backup code.
     */
    public function verifyBackupCode(string $identifier, string $code): bool
    {
        return (new VerifyBackupCode)->execute($identifier, $code);
    }

    /**
     * Generate a Magic Login Link.
     */
    public function generateMagicLink(string $identifier): string
    {
        return (new GenerateMagicLink)->execute($identifier, $this->expiry);
    }
}
