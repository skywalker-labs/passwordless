<?php

namespace Skywalker\Otp\Tests;

use Skywalker\Otp\Tests\TestCase;

class ResolverTest extends TestCase
{
    public function test_can_resolve_otp_service()
    {
        $service = app('otp');
        $this->assertNotNull($service);
        $this->assertInstanceOf(\Skywalker\Otp\Services\OtpService::class, $service);
    }
}
