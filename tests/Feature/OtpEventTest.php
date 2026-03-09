<?php

namespace Skywalker\Otp\Tests\Feature;

use Skywalker\Otp\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Skywalker\Otp\Events\OtpVerified;
use Skywalker\Otp\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class OtpEventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    public function test_otp_verified_event_is_fired_from_controller()
    {
        Event::fake([OtpVerified::class]);

        // Create a user
        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'password' => bcrypt('password'), // Required by some models
        ]);

        $service = app('otp');
        $otp = $service->generate('test@example.com');

        $response = $this->postJson(route('otp.verify'), [
            'identifier' => 'test@example.com',
            'otp' => $otp,
        ]);

        $response->assertStatus(200);
        
        Event::assertDispatched(OtpVerified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_magic_link_verified_event_is_fired_from_controller()
    {
        Event::fake([OtpVerified::class]);

        // Create a user
        $userModel = config('auth.providers.users.model');
        $user = $userModel::create([
            'email' => 'magic@example.com',
            'name' => 'Magic User',
            'password' => bcrypt('password'),
        ]);

        $service = app('otp');
        $link = $service->generateMagicLink('magic@example.com');

        $response = $this->get($link);

        $response->assertRedirect('/'); // Default intended
        
        Event::assertDispatched(OtpVerified::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }
}
