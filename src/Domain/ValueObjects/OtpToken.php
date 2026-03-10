<?php

declare(strict_types=1);

namespace Skywalker\Otp\Domain\ValueObjects;

use Carbon\Carbon;
use Skywalker\Support\Foundation\ValueObject;

/**
 * @readonly
 */
class OtpToken extends ValueObject
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $hashedToken,
        public readonly Carbon $expiresAt
    ) {}

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiresAt->isPast();
    }

    public function __toString(): string
    {
        $json = json_encode([
            'identifier' => $this->identifier,
            'hashedToken' => $this->hashedToken,
            'expiresAt' => $this->expiresAt->toIso8601String(),
        ]);

        return is_string($json) ? $json : '';
    }
}
