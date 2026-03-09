<?php

declare(strict_types=1);

namespace Skywalker\Otp\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Skywalker\Otp\Exceptions\OtpException;

class OtpFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The identifier for which the OTP failed to send.
     *
     * @var string
     */
    public $identifier;

    /**
     * The exception that caused the failure.
     *
     * @var OtpException
     */
    public $exception;

    /**
     * Create a new event instance.
     *
     * @param string $identifier
     * @param OtpException $exception
     */
    public function __construct(string $identifier, OtpException $exception)
    {
        $this->identifier = $identifier;
        $this->exception = $exception;
    }
}
