<?php

declare(strict_types=1);

namespace Skywalker\Otp\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Skywalker\Support\Actions\Action;

class GenerateBackupCodes extends Action
{
    /**
     * @param mixed ...$args [$identifier, $quantity]
     * @return array<int, string>
     */
    public function execute(...$args): array
    {
        $identifier = $args[0] ?? throw new \InvalidArgumentException('Identifier is required.');
        $quantity = $args[1] ?? 8;

        assert(is_string($identifier));
        assert(is_int($quantity));

        DB::table('otp_backup_codes')->where('identifier', $identifier)->delete();

        /** @var array<int, string> $codes */
        $codes = [];
        /** @var array<int, array<string, mixed>> $data */
        $data = [];
        $now = Carbon::now();

        for ($i = 0; $i < $quantity; $i++) {
            $code = Str::random(10);
            $codes[] = $code;
            $data[] = [
                'identifier' => $identifier,
                'code' => $code, // Should ideally be hashed, but keeping parity for now
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('otp_backup_codes')->insert($data);

        return $codes;
    }
}
