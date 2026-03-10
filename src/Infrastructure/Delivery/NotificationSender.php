<?php

declare(strict_types=1);

namespace Skywalker\Otp\Infrastructure\Delivery;

use Exception;
use Illuminate\Support\Facades\Notification;
use Skywalker\Otp\Domain\Contracts\OtpSender;
use Skywalker\Otp\Exceptions\OtpDeliveryFailedException;
use Skywalker\Otp\Notifications\OtpNotification;

class NotificationSender implements OtpSender
{
    /**
     * {@inheritDoc}
     */
    public function send(string $identifier, string $token, string $channel): void
    {
        try {
            if ($channel === 'log') {
                // Handled directly or via a specific channel if needed
                // For now, let's keep it clean
            }

            $routeKey = $this->resolveRouteKey($channel);

            Notification::route($routeKey, $identifier)
                ->notify(new OtpNotification($token));

        } catch (Exception $e) {
            throw new OtpDeliveryFailedException("Failed to send OTP via {$channel}: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Resolve the notification route key based on channel.
     */
    protected function resolveRouteKey(string $channel): string
    {
        return match ($channel) {
            'sms' => 'sms',
            'slack' => 'slack',
            default => 'mail',
        };
    }
}
