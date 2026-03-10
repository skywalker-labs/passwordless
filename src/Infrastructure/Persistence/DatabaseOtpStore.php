<?php

declare(strict_types=1);

namespace Skywalker\Otp\Infrastructure\Persistence;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Skywalker\Otp\Domain\Contracts\OtpStore;
use Skywalker\Otp\Domain\ValueObjects\OtpToken;

class DatabaseOtpStore implements OtpStore
{
    /**
     * {@inheritDoc}
     */
    public function store(OtpToken $token): void
    {
        DB::table('otps')->updateOrInsert(
            ['identifier' => $token->identifier],
            [
                'token' => $token->hashedToken,
                'expires_at' => $token->expiresAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $identifier): ?OtpToken
    {
        /** @var object{identifier: string, token: string, expires_at: string}|null $record */
        $record = DB::table('otps')
            ->where('identifier', $identifier)
            ->first();

        if ($record === null) {
            return null;
        }

        return new OtpToken(
            identifier: $record->identifier,
            hashedToken: $record->token,
            expiresAt: Carbon::parse($record->expires_at)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $identifier): void
    {
        DB::table('otps')->where('identifier', $identifier)->delete();
    }
}
