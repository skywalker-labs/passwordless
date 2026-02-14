<?php

namespace Skywalker\Otp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Skywalker\Support\Logging\Concerns\HasContext;

class OtpService
{
    use HasContext;

    protected $length;
    protected $expiry;
    protected $driver;
    protected $channel;
    protected $identifier;

    public function __construct()
    {
        $this->length = config('passwordless.length', 6);
        $this->expiry = config('passwordless.expiry', 10);
        $this->driver = config('passwordless.driver', 'cache');
        $this->channel = config('passwordless.channel', 'log');
    }

    public function generate(string $identifier): string
    {
        $this->identifier = $identifier;
        $otp = $this->generateToken();

        $this->store($identifier, $otp);
        $this->send($identifier, $otp);

        return $otp;
    }

    public function verify(string $identifier, string $token): bool
    {
        if ($this->driver === 'database') {
            $record = DB::table('otps')
                ->where('identifier', $identifier)
                ->where('token', $token)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($record) {
                DB::table('otps')->where('id', $record->id)->delete(); // One-time use
                return true;
            }
        } else {
            $key = 'otp_' . $identifier;
            if (Cache::get($key) === $token) {
                Cache::forget($key);
                return true;
            }
        }

        return false;
    }

    protected function generateToken(): string
    {
        return (string) random_int(pow(10, $this->length - 1), pow(10, $this->length) - 1);
    }

    protected function store(string $identifier, string $token): void
    {
        if ($this->driver === 'database') {
            DB::table('otps')->updateOrInsert(
                ['identifier' => $identifier],
                [
                    'token' => $token,
                    'expires_at' => Carbon::now()->addMinutes($this->expiry),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        } else {
            Cache::put('otp_' . $identifier, $token, Carbon::now()->addMinutes($this->expiry));
        }
    }

    /**
     * Generate a set of backup codes for the user.
     *
     * @param string $identifier
     * @param int $quantity
     * @return array
     */
    public function generateBackupCodes(string $identifier, int $quantity = 8): array
    {
        // Clear existing codes? Maybe not, allow appending or explicit clear. 
        // For security, usually regenerating invalidates old ones.
        DB::table('otp_backup_codes')->where('identifier', $identifier)->delete();

        $codes = [];
        $data = [];
        $now = Carbon::now();

        for ($i = 0; $i < $quantity; $i++) {
            $code = Str::random(10); // 10 chars alphanumeric
            $codes[] = $code;
            $data[] = [
                'identifier' => $identifier,
                'code' => $code, // In production, these should be hashed! But for simplicity keeping plain for now.
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('otp_backup_codes')->insert($data);

        $this->logWithContext('info', "Generated {$quantity} backup codes for {$identifier}");

        return $codes;
    }

    /**
     * Verify and consume a backup code.
     *
     * @param string $identifier
     * @param string $code
     * @return bool
     */
    public function verifyBackupCode(string $identifier, string $code): bool
    {
        $record = DB::table('otp_backup_codes')
            ->where('identifier', $identifier)
            ->where('code', $code)
            ->whereNull('used_at')
            ->first();

        if ($record) {
            DB::table('otp_backup_codes')
                ->where('id', $record->id)
                ->update(['used_at' => Carbon::now()]);
            
            $this->logWithContext('info', "Backup code used for {$identifier}");
            
            return true;
        }

        return false;
    }

    /**
     * Generate a Magic Login Link.
     *
     * @param string $identifier
     * @return string
     */
    public function generateMagicLink(string $identifier): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'passwordless.magic-login',
            \Carbon\Carbon::now()->addMinutes($this->expiry),
            ['identifier' => $identifier]
        );
    }

    protected function send(string $identifier, string $token): void
    {
        if ($this->channel === 'log') {
            $this->logWithContext('info', "OTP for {$identifier}: {$token}", ['identifier' => $identifier]);
            return;
        }

        $routeKey = 'mail';
        if ($this->channel === 'sms') $routeKey = 'sms';
        if ($this->channel === 'slack') $routeKey = 'slack';

        \Illuminate\Support\Facades\Notification::route($routeKey, $identifier)
            ->notify(new \Skywalker\Otp\Notifications\OtpNotification($token));
    }
}
