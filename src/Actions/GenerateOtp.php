<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Skywalker\Otp\Domain\Contracts\OtpSender;
use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Domain\ValueObjects\OtpToken;
use Skywalker\Otp\Events\OtpFailed;
use Skywalker\Otp\Events\OtpGenerated;
use Skywalker\Otp\Exceptions\OtpException;
use Skywalker\Support\Foundation\Action;

class GenerateOtp extends Action
{
    protected OtpStore $store;

    protected OtpSender $sender;

    public function __construct(
        ?OtpStore $store = null,
        ?OtpSender $sender = null
    ) {
        $this->store = $store ?? app(OtpStore::class);
        $this->sender = $sender ?? app(OtpSender::class);
    }

    /**
     * @param  mixed  ...$args  [string $identifier, ?int $length, ?int $expiry, ?string $channel, ?\Closure $generator]
     *
     * @throws OtpException
     */
    public function execute(...$args): string
    {
        $identifier = $args[0] ?? throw new \InvalidArgumentException('Identifier is required.');
        $length = $args[1] ?? null;
        $expiry = $args[2] ?? null;
        $channel = $args[3] ?? null;
        $generator = $args[4] ?? null;

        assert(is_string($identifier));
        assert($length === null || is_int($length));
        assert($expiry === null || is_int($expiry));
        assert($channel === null || is_string($channel));
        assert($generator === null || $generator instanceof \Closure);
        $configLength = config('passwordless.length', 6);
        $length = $length ?? (is_int($configLength) ? $configLength : 6);

        $configExpiry = config('passwordless.expiry', 15);
        $expiry = $expiry ?? (is_int($configExpiry) ? $configExpiry : 15);

        $configChannel = config('passwordless.default_channel', 'mail');
        $channel = $channel ?? (is_string($configChannel) ? $configChannel : 'mail');

        $otp = $this->generateToken($length, $generator);

        $token = new OtpToken(
            identifier: $identifier,
            hashedToken: Hash::make($otp),
            expiresAt: Carbon::now()->addMinutes($expiry)
        );

        $this->store->store($token);

        try {
            $this->sender->send($identifier, $otp, $channel);
            OtpGenerated::dispatch($identifier, $otp);
        } catch (OtpException $e) {
            OtpFailed::dispatch($identifier, $e);
            throw $e;
        }

        return $otp;
    }

    protected function generateToken(int $length, ?\Closure $generator): string
    {
        if ($generator instanceof \Closure) {
            $result = $generator();
            if (! is_string($result)) {
                throw new \RuntimeException('Custom OTP generator must return a string.');
            }

            return $result;
        }

        return (string) random_int(pow(10, $length - 1), pow(10, $length) - 1);
    }
}
