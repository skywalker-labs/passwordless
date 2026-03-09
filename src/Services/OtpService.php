<?php

declare(strict_types=1);

namespace Skywalker\Otp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Skywalker\Otp\Domain\Contracts\OtpService as OtpServiceContract;
use Skywalker\Otp\Exceptions\InvalidOtpException;
use Skywalker\Otp\Exceptions\OtpDeliveryFailedException;
use Skywalker\Support\Services\BaseService;

use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Domain\Contracts\OtpSender;
use Skywalker\Otp\Domain\ValueObjects\OtpToken;

use Skywalker\Otp\Actions\GenerateOtp;
use Skywalker\Otp\Actions\VerifyOtp;
use Skywalker\Otp\Actions\GenerateBackupCodes;
use Skywalker\Otp\Actions\VerifyBackupCode;
use Skywalker\Otp\Actions\GenerateMagicLink;

class OtpService extends BaseService implements OtpServiceContract
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
        $action = new GenerateOtp();
        return $action->execute($this->store, $this->sender, $identifier, $this->length, $this->expiry, $this->channel, static::$generator);
    }

    /**
     * Set a custom OTP generator.
     *
     * @param (\Closure(): string) $callback
     * @return void
     */
    public static function useGenerator(\Closure $callback): void
    {
        static::$generator = $callback;
    }

    /**
     * Verify the OTP token.
     *
     * @param string $identifier
     * @param string $token
     * @return bool
     * @throws InvalidOtpException
     */
    public function verify(string $identifier, string $token): bool
    {
        $action = new VerifyOtp();
        return $action->execute($this->store, $identifier, $token);
    }

    /**
     * Generate a set of backup codes for the user.
     *
     * @param string $identifier
     * @param int $quantity
     * @return array<int, string>
     */
    public function generateBackupCodes(string $identifier, int $quantity = 8): array
    {
        $action = new GenerateBackupCodes();
        return $action->execute($identifier, $quantity);
    }

    /**
     * Verify and consume a backup code.
     *
     * @param string $identifier
     * @param string $code
     * @return bool
     */
    public function verifyBackupCode(string $identifier, string $code): bool
    {
        $action = new VerifyBackupCode();
        return $action->execute($identifier, $code);
    }

    /**
     * Generate a Magic Login Link.
     *
     * @param string $identifier
     * @return string
     */
    public function generateMagicLink(string $identifier): string
    {
        $action = new GenerateMagicLink();
        return $action->execute($identifier, $this->expiry);
    }
}
