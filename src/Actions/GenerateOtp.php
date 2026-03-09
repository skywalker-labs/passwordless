<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Skywalker\Otp\Domain\Contracts\OtpSender;
use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Events\OtpGenerated;
use Skywalker\Otp\Events\OtpFailed;
use Skywalker\Otp\Exceptions\OtpException;
use Skywalker\Otp\Domain\ValueObjects\OtpToken;
use Skywalker\Support\Actions\Action;

class GenerateOtp extends Action
{
    /**
     * @param mixed ...$args [OtpStore, OtpSender, string $identifier, int $length, int $expiry, string $channel, \Closure|null $generator]
     * @return string
     * @throws OtpException
     */
    public function execute(...$args): string
    {
        $store      = $args[0] ?? throw new \InvalidArgumentException('OtpStore is required.');
        $sender     = $args[1] ?? throw new \InvalidArgumentException('OtpSender is required.');
        $identifier = $args[2] ?? throw new \InvalidArgumentException('Identifier is required.');
        $length     = $args[3] ?? config('passwordless.length', 6);
        $expiry     = $args[4] ?? config('passwordless.expiry', 15);
        $channel    = $args[5] ?? config('passwordless.default_channel', 'mail');
        $generator  = $args[6] ?? null;

        assert($store instanceof OtpStore);
        assert($sender instanceof OtpSender);
        assert(is_string($identifier));
        assert(is_int($length));
        assert(is_int($expiry));
        assert(is_string($channel));

        $otp = $this->generateToken($length, $generator);

        $token = new OtpToken(
            identifier: $identifier,
            hashedToken: Hash::make($otp),
            expiresAt: Carbon::now()->addMinutes($expiry)
        );

        $store->store($token);

        try {
            $sender->send($identifier, $otp, $channel);
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
            if (!is_string($result)) {
                throw new \RuntimeException('Custom OTP generator must return a string.');
            }
            return $result;
        }

        return (string) random_int(pow(10, $length - 1), pow(10, $length) - 1);
    }
}
