<?php

namespace Skywalker\Otp\Tests\Feature;

use Skywalker\Otp\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class MiddlewareTest extends TestCase
{
    public function test_package_routes_have_configured_middleware()
    {
        $routes = Route::getRoutes();
        
        $sendOtpRoute = $routes->getByName('otp.send');
        $verifyOtpRoute = $routes->getByName('otp.verify');
        $magicLoginRoute = $routes->getByName('passwordless.magic-login');

        $this->assertNotNull($sendOtpRoute);
        $this->assertNotNull($verifyOtpRoute);
        $this->assertNotNull($magicLoginRoute);

        // Config sets: ['web', 'throttle:6,1']
        $expectedMiddleware = ['web', 'throttle:6,1'];

        foreach ($expectedMiddleware as $middleware) {
            $this->assertContains($middleware, $sendOtpRoute->gatherMiddleware());
            $this->assertContains($middleware, $verifyOtpRoute->gatherMiddleware());
            $this->assertContains($middleware, $magicLoginRoute->gatherMiddleware());
        }
    }
}
