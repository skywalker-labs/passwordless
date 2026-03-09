<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Skywalker\Support\Actions\Action;

class VerifyBackupCode extends Action
{
    /**
     * @param mixed ...$args [$identifier, $code]
     * @return bool
     */
    public function execute(...$args): bool
    {
        $identifier = $args[0] ?? throw new \InvalidArgumentException('Identifier is required.');
        $code = $args[1] ?? throw new \InvalidArgumentException('Code is required.');

        assert(is_string($identifier));
        assert(is_string($code));

        /** @var object{id: int, identifier: string, code: string, used_at: string|null}|null $record */
        $record = DB::table('otp_backup_codes')
            ->where('identifier', $identifier)
            ->where('code', $code)
            ->whereNull('used_at')
            ->first();

        if ($record !== null) {
            DB::table('otp_backup_codes')
                ->where('id', $record->id)
                ->update(['used_at' => Carbon::now()]);

            return true;
        }

        return false;
    }
}
