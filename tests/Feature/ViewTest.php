<?php

namespace Skywalker\Otp\Tests\Feature;

use Skywalker\Otp\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\User;

class ViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Define a test user model
        $this->app['config']->set('auth.providers.users.model', User::class);
        
        // Ensure routes are loaded (TestCase does this via ServiceProvider, but ensuring strictness)
    }

    public function test_otp_verify_view_is_accessible()
    {
        // migrate the user table for the test user
        $this->loadLaravelMigrations();
        
        $user = new User();
        $user->email = 'test@example.com';
        $user->name = 'Test User';
        $user->password = bcrypt('password');
        $user->save();

        $response = $this->actingAs($user)
                         ->get(route('otp.verify.view'));

        $response->assertStatus(200);
        $response->assertViewIs('passwordless::otp-verify');
        $response->assertSee('OTP Verification'); // Verify content from blade
    }

    public function test_guest_cannot_access_otp_view()
    {
        $response = $this->get(route('otp.verify.view'));

        $response->assertRedirect(); // Should redirect to login
    }
}
