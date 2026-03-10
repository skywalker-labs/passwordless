<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Skywalker\Support\Foundation\Action;

class VerifyBackupCode extends Action
{
    /**
     * @param  string  $identifier
     * @param  string  $code
     */
    /**
     * @param  mixed  ...$args  [string $identifier, string $code]
     */
    public function execute(...$args): bool
    {
        $identifier = $args[0] ?? throw new \InvalidArgumentException('Identifier is required.');
        $code = $args[1] ?? throw new \InvalidArgumentException('Code is required.');

        assert(is_string($identifier));
        assert(is_string($code));
        /** @var array<int, \stdClass> $records */
        $records = DB::table('otp_backup_codes')
            ->where('identifier', $identifier)
            ->whereNull('used_at')
            ->get()
            ->all();

        foreach ($records as $record) {
            if (Hash::check($code, $record->code)) {
                DB::table('otp_backup_codes')
                    ->where('id', $record->id)
                    ->update(['used_at' => Carbon::now()]);

                return true;
            }
        }

        return false;
    }
}
