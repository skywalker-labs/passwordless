<?php

namespace Skywalker\Otp;

use Skywalker\Support\Providers\PackageServiceProvider;

class OtpServiceProvider extends PackageServiceProvider
{
    /**
     * Vendor name.
     *
     * @var string
     */
    protected $vendor = 'skywalker-labs';

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'passwordless';

    /**
     * Register services.
     */
    public function register()
    {
        parent::register();

        $this->registerConfig();

        $this->app->singleton('otp', function ($app) {
            return new \Skywalker\Otp\Services\OtpService();
        });

        $this->registerCommands([
            \Skywalker\Otp\Console\Commands\CleanExpiredOtps::class,
        ]);

        // Register Event Listener
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \Skywalker\Otp\Listeners\SendOtpListener::class
        );

        // Register Middleware Alias
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $router->aliasMiddleware('otp.verified', \Skywalker\Otp\Http\Middleware\EnsureOtpVerified::class);

        // Auto-push middleware to web group for seamless integration
        $router->pushMiddlewareToGroup('web', \Skywalker\Otp\Http\Middleware\EnsureOtpVerified::class);
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
        parent::boot();

        $this->publishConfig();
        $this->publishMigrations();
        $this->loadViews();
        $this->loadMigrations();

        // Load routes with configured middleware
        \Illuminate\Support\Facades\Route::middleware(config('passwordless.middleware', ['web']))
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
    }

    /**
     * Get the base views path.
     *
     * @return string
     */
    protected function getViewsPath(): string
    {
        return $this->getBasePath() . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
    }
}
