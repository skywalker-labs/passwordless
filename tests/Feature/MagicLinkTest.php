<?php

namespace Skywalker\Otp\Tests\Feature;

use Skywalker\Otp\Tests\TestCase;
use Illuminate\Support\Facades\URL;

class MagicLinkTest extends TestCase
{
    public function test_can_generate_magic_link()
    {
        $service = app('otp');
        $link = $service->generateMagicLink('test@example.com');

        $this->assertNotNull($link);
        $this->assertStringContainsString('magic-login', $link);
        $this->assertStringContainsString('signature=', $link);
    }
}
