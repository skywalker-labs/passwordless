<?php

namespace Skywalker\Otp\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Skywalker\Support\Logging\Concerns\HasContext;

class CleanExpiredOtps extends Command
{
    use HasContext;

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
        $driver = config('passwordless.driver');

        if ($driver !== 'database') {
            $this->error('OTP driver is not set to database. Cleaning skipped.');
            return 0;
        }

        $count = DB::table('otps')
            ->where('expires_at', '<', Carbon::now())
            ->delete();

        $this->info("Deleted {$count} expired OTPs.");

        $this->logWithContext('info', "Cleaned expired OTPs", ['count' => $count]);

        return 0;
    }
}
