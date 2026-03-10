<?php

declare(strict_types=1);

namespace Skywalker\Otp\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;
use Skywalker\Otp\Concerns\HasOtp;

class SendOtpListener
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Check if user has the HasOtp trait or method
        if (method_exists($user, 'sendOtp')) {
            $user->sendOtp();

            // Set session flag to false (unverified)
            Session::put('otp_verified', false);
        }
    }
}
