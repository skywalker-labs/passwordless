<?php

namespace Skywalker\Otp\Tests\Feature;

use Skywalker\Otp\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class BackupCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_backup_codes()
    {
        $service = app('otp');
        $codes = $service->generateBackupCodes('test@example.com', 5);

        $this->assertCount(5, $codes);
        $this->assertEquals(5, DB::table('otp_backup_codes')->count());
    }

    public function test_can_verify_backup_code()
    {
        $service = app('otp');
        $codes = $service->generateBackupCodes('test@example.com');
        $code = $codes[0];

        $this->assertTrue($service->verifyBackupCode('test@example.com', $code));
        
        // Should be used now
        $this->assertFalse($service->verifyBackupCode('test@example.com', $code));
    }
}
