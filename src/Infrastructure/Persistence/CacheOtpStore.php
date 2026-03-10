<?php

declare(strict_types=1);

namespace Skywalker\Otp\Infrastructure\Persistence;

use Illuminate\Support\Facades\Cache;
use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Domain\ValueObjects\OtpToken;

class CacheOtpStore implements OtpStore
{
    /**
     * {@inheritDoc}
     */
    public function store(OtpToken $token): void
    {
        Cache::put(
            $this->getCacheKey($token->identifier),
            $token,
            $token->expiresAt
        );
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $identifier): ?OtpToken
    {
        $token = Cache::get($this->getCacheKey($identifier));

        return $token instanceof OtpToken ? $token : null;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $identifier): void
    {
        Cache::forget($this->getCacheKey($identifier));
    }

    /**
     * Get the cache key for the identifier.
     */
    protected function getCacheKey(string $identifier): string
    {
        return 'otp_'.$identifier;
    }
}
