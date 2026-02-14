<?php

namespace Skywalker\Otp\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Skywalker\Otp\OtpServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \Skywalker\Otp\OtpServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        
        $app['config']->set('passwordless.driver', 'cache'); // Default
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    protected function defineDatabaseMigrations()
    {
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function defineRoutes($router)
    {
        $router->get('/login', function () {
            return 'login';
        })->name('login');

        $router->post('/logout', function () {
            return 'logout';
        })->name('logout');
    }
}
