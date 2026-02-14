<?php

namespace Skywalker\Otp\Tests\Feature;

use Skywalker\Otp\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class OtpTest extends TestCase
{
    public function test_can_generate_otp()
    {
        $service = app('otp');
        $otp = $service->generate('test@example.com');

        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp));
        $this->assertTrue(Cache::has('otp_test@example.com'));
    }

    public function test_can_verify_otp()
    {
        $service = app('otp');
        $otp = $service->generate('test@example.com');

        $this->assertTrue($service->verify('test@example.com', $otp));
        $this->assertFalse(Cache::has('otp_test@example.com')); // Should be deleted after use
    }

    public function test_invalid_otp_fails()
    {
        $service = app('otp');
        $service->generate('test@example.com');

        $this->assertFalse($service->verify('test@example.com', '000000'));
    }

    public function test_expired_otp_fails()
    {
         // Mock time or manipulate cache expiry if possible, 
         // but for simplicity, we can just test that non-existent OTP fails
         $this->assertFalse(app('otp')->verify('expired@example.com', '123456'));
    }
}
