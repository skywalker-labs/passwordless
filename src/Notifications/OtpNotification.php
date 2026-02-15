<?php

namespace Skywalker\Otp\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $otp;
    /** @var array<int, string>|string|null */
    protected $channels;

    /**
     * Create a new notification instance.
     *
     * @param string $otp
     * @param array<int, string>|string|null $channels
     */
    public function __construct(string $otp, $channels = null)
    {
        $this->otp = $otp;
        $this->channels = $channels;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        // If channels passed explicitly, use them
        if ($this->channels) {
            return (array) $this->channels;
        }

        // Smart detection based on configuration or identifier
        $configuredChannel = config('passwordless.channel', 'mail');

        // If the notifiable is an anonymous route (e.g. Notification::route('mail', ...))
        // standard Laravel routing applies.

        // If strictly checking config:
        if ($configuredChannel === 'sms') {
            // For now, returning a custom channel class or 'nexmo'/'twilio' if installed
            // We will implement a basic custom TwilioChannel for this package
            return [\Skywalker\Otp\Notifications\Channels\TwilioChannel::class];
        }

        if ($configuredChannel === 'slack') {
            return ['slack'];
        }

        if ($configuredChannel === 'log') {
            return [\Skywalker\Otp\Notifications\Channels\LogChannel::class];
        }

        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $expiryConfig = config('passwordless.expiry', '10');
        $expiry = is_string($expiryConfig) ? $expiryConfig : (is_int($expiryConfig) ? (string) $expiryConfig : '10');

        return (new MailMessage)
            ->subject('Your Login OTP')
            ->line('Your One-Time Password is: ' . $this->otp)
            ->line('This code will expire in ' . $expiry . ' minutes.')
            ->line('Do not share this code with anyone.');
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return mixed
     */
    public function toSlack($notifiable): mixed
    {
        if (class_exists('Illuminate\\Notifications\\Messages\\SlackMessage')) {
            /** @var \Illuminate\Notifications\Messages\SlackMessage $message */
            $message = new \Illuminate\Notifications\Messages\SlackMessage();
            return $message->content('Your OTP Code is: ' . $this->otp);
        }
        return null;
    }

    /**
     * Get the Twilio/SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    public function toSms($notifiable)
    {
        return "Your OTP code is: {$this->otp}";
    }
}
