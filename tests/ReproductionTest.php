<?php

namespace Skywalker\Otp\Tests;

use Skywalker\Otp\Tests\TestCase;

class ReproductionTest extends TestCase
{
    public function test_simple_assertion()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_redirect_assertion()
    {
        $response = $this->post('/logout');
        $response->assertStatus(200); // TestCase defines it as returning 'logout' string, so it should be 200
    }
}
