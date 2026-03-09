<?php

declare(strict_types=1);

namespace Skywalker\Otp\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;

class OtpVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly Authenticatable $user,
        public readonly Request $request
    ) {}
}
