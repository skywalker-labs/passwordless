<?php

namespace Skywalker\Otp\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TwilioChannel
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
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        
        // Get phone number from notifiable
        // Assuming notifiable has phone_number or routeNotificationFor('sms')
        $to = $notifiable->routeNotificationFor('sms') ?? $notifiable->phone ?? null;

        if (!$to) {
            Log::error('TwilioChannel: No phone number found for notifiable.');
            return;
        }

        $sid = config('passwordless.services.twilio.sid');
        $token = config('passwordless.services.twilio.token');
        $from = config('passwordless.services.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::error('TwilioChannel: Missing Configuration.');
            return;
        }

        // Send request to Twilio API
        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        if (!$response->successful()) {
            Log::error('TwilioChannel: Failed to send SMS.', ['response' => $response->body()]);
        }
    }
}
