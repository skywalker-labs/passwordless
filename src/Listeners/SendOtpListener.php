<?php

namespace Skywalker\Otp\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Session;
use Skywalker\Otp\Traits\HasOtp;

class SendOtpListener
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
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
