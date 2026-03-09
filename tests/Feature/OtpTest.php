<?php

namespace Skywalker\Otp\Tests\Feature;

use Skywalker\Otp\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Skywalker\Otp\Exceptions\InvalidOtpException;
use Skywalker\Otp\Events\OtpVerified;
use Skywalker\Otp\Services\OtpService;

class OtpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function test_can_generate_otp()
    {
        $service = app('otp');
        $otp = $service->generate('test@example.com');

        $this->assertNotNull($otp);
        $this->assertEquals(6, strlen($otp));
        
        /** @var \Skywalker\Otp\Domain\ValueObjects\OtpToken $storedToken */
        $storedToken = Cache::get('otp_test@example.com');
        $this->assertInstanceOf(\Skywalker\Otp\Domain\ValueObjects\OtpToken::class, $storedToken);
        $this->assertTrue(Hash::check($otp, $storedToken->hashedToken));
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
        $this->expectException(InvalidOtpException::class);

        $service = app('otp');
        $service->generate('test@example.com');

        $service->verify('test@example.com', '000000');
    }

    public function test_expired_otp_fails()
    {
         $this->expectException(InvalidOtpException::class);
         
         app('otp')->verify('expired@example.com', '123456');
    }

    public function test_can_use_custom_generator()
    {
        OtpService::useGenerator(fn() => '1234');

        $service = app('otp');
        $otp = $service->generate('test@example.com');

        $this->assertEquals('1234', $otp);
        $this->assertTrue($service->verify('test@example.com', '1234'));
    }
}
