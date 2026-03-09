<?php

declare(strict_types=1);

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
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);
        
        // Get phone number from notifiable
        /** @var string|null $to */
        $to = null;

        if (is_object($notifiable) || is_string($notifiable)) {
            /** @phpstan-ignore-next-line */
            $routeTo = $notifiable->routeNotificationFor('sms');
            $to = is_string($routeTo) ? $routeTo : null;

            if (!$to && is_object($notifiable) && property_exists($notifiable, 'phone')) {
                $phone = $notifiable->phone;
                $to = is_string($phone) ? $phone : null;
            }
        }

        if (!$to) {
            Log::error('TwilioChannel: No phone number found for notifiable.');
            return;
        }

        $sidConfig = config('passwordless.services.twilio.sid');
        $sid = is_string($sidConfig) ? $sidConfig : '';

        $tokenConfig = config('passwordless.services.twilio.token');
        $token = is_string($tokenConfig) ? $tokenConfig : '';

        $fromConfig = config('passwordless.services.twilio.from');
        $from = is_string($fromConfig) ? $fromConfig : '';

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
