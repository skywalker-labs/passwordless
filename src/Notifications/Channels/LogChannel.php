<?php

namespace Skywalker\Otp\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class LogChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = 'Notification: ';
        if (method_exists($notification, 'toSms')) {
            $message .= $notification->toSms($notifiable);
        } elseif (method_exists($notification, 'toMail')) {
             // Basic implementation for log
             $mail = $notification->toMail($notifiable);
             $message .= implode("\n", $mail->introLines);
        }

        Log::info($message);
    }
}
