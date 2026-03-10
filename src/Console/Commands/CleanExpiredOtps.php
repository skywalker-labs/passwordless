<?php

declare(strict_types=1);

namespace Skywalker\Otp\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanExpiredOtps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passwordless:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired OTPs from the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $driver = config('passwordless.driver', 'cache');
        $driver = is_string($driver) ? $driver : 'cache';

        if ($driver !== 'database') {
            $this->error('OTP driver is not set to database. Cleaning skipped.');

            return 0;
        }

        $count = DB::table('otps')
            ->where('expires_at', '<', Carbon::now())
            ->delete();

        $this->info("Deleted {$count} expired OTPs.");

        Log::info('Cleaned expired OTPs', ['count' => $count]);

        return 0;
    }
}
