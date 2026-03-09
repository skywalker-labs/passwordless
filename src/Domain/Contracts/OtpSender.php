<?php

declare(strict_types=1);

namespace Skywalker\Otp\Domain\Contracts;

interface OtpSender
{
    /**
     * Send the OTP token to the identifier.
     *
     * @param string $identifier
     * @param string $token
     * @param string $channel
     * @return void
     * @throws \Skywalker\Otp\Exceptions\OtpDeliveryFailedException
     */
    public function send(string $identifier, string $token, string $channel): void;
}
