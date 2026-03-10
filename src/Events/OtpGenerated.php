<?php

declare(strict_types=1);

namespace Skywalker\Otp\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OtpGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The identifier for which the OTP was generated.
     *
     * @var string
     */
    public $identifier;

    /**
     * The generated (plain-text) OTP.
     *
     * @var string
     */
    public $otp;

    /**
     * Create a new event instance.
     */
    public function __construct(string $identifier, string $otp)
    {
        $this->identifier = $identifier;
        $this->otp = $otp;
    }
}
