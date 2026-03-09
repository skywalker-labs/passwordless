<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Skywalker\Support\Actions\Action;

class GenerateMagicLink extends Action
{
    /**
     * @param mixed ...$args [$identifier, $expiry]
     * @return string
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
