<?php

declare(strict_types=1);

namespace Skywalker\Otp\Domain\ValueObjects;

use Carbon\Carbon;
use Skywalker\Support\Data\ValueObject;

/**
 * @readonly
 */
class OtpToken extends ValueObject
{
    /**
     * @param string $identifier
     * @param string $hashedToken
     * @param Carbon $expiresAt
     */
    public function __construct(
        public string $identifier,
        public string $hashedToken,
        public Carbon $expiresAt
    ) {
    }

    /**
     * Check if the token is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiresAt->isPast();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode([
            'identifier' => $this->identifier,
            'hashedToken' => $this->hashedToken,
            'expiresAt' => $this->expiresAt->toIso8601String(),
        ]) ?: '';
    }
}
