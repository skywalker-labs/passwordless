<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Skywalker\Support\Foundation\Action;

class GenerateMagicLink extends Action
{
    /**
     * @param  string  $identifier
     * @param  int  $expiry
     */
    /**
     * @param  mixed  ...$args  [string $identifier, int $expiry]
     */
    public function execute(...$args): string
    {
        $identifier = $args[0] ?? throw new \InvalidArgumentException('Identifier is required.');
        $expiry = $args[1] ?? 15;

        assert(is_string($identifier));
        assert(is_int($expiry));

        return URL::temporarySignedRoute(
            'passwordless.magic-login',
            Carbon::now()->addMinutes($expiry),
            ['identifier' => $identifier]
        );
    }
}
