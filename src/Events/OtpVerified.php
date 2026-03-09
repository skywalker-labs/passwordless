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
     * The verified user.
     *
     * @var Authenticatable
     */
    public $user;

    /**
     * The current request.
     *
     * @var Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param Authenticatable $user
     * @param Request $request
     */
    public function __construct(Authenticatable $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }
}
