<?php

declare(strict_types=1);

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
     * @var string|null
     */
    protected $package = 'passwordless';

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();

        $this->registerConfig();

        $this->app->singleton(\Skywalker\Otp\Domain\Contracts\OtpStore::class, function ($app) {
            $driver = config('passwordless.driver', 'cache');

            return (is_string($driver) && $driver === 'database')
                ? new \Skywalker\Otp\Infrastructure\Persistence\DatabaseOtpStore
                : new \Skywalker\Otp\Infrastructure\Persistence\CacheOtpStore;
        });

        $this->app->singleton(\Skywalker\Otp\Domain\Contracts\OtpSender::class, function ($app) {
            return new \Skywalker\Otp\Infrastructure\Delivery\NotificationSender;
        });

        $this->app->singleton(\Skywalker\Otp\Domain\Contracts\OtpService::class, function ($app) {
            return new \Skywalker\Otp\Services\OtpService(
                $app->make(\Skywalker\Otp\Domain\Contracts\OtpStore::class),
                $app->make(\Skywalker\Otp\Domain\Contracts\OtpSender::class)
            );
        });

        $this->app->alias(\Skywalker\Otp\Domain\Contracts\OtpService::class, 'otp');

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
        $middleware = config('passwordless.middleware', ['web']);
        $middleware = is_array($middleware) ? $middleware : ['web'];

        \Illuminate\Support\Facades\Route::middleware($middleware)
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
    }

    /**
     * Get the base views path.
     */
    protected function getViewsPath(): string
    {
        return $this->getBasePath().DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views';
    }
}
