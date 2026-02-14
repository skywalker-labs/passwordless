<?php

namespace Skywalker\Otp;

use Skywalker\Support\Providers\PackageServiceProvider;

class OtpServiceProvider extends PackageServiceProvider
{
    protected $vendor = 'skywalker-labs';
    protected $package = 'passwordless';

    /**
     * Register services.
     */
    public function register()
    {
        parent::register();

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
    public function boot()
    {
        parent::boot();
        
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'passwordless');
        $this->publishAll();
        
        // Load routes manually as PackageServiceProvider doesn't enforce route structure
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    protected function publishAll(): void
    {
        // $this->publishAssets();
        $this->publishConfig();
        // $this->publishFactories();
        // $this->publishMigrations();
        // $this->publishTranslations();
        // $this->publishViews();
    }

    protected function publishConfig(?string $path = null): void
    {
        // $this->publishes([
        //     __DIR__.'/../config/passwordless.php' => config_path('passwordless.php'),
        // ], 'config');
    }


    /**
     * Get the base views path.
     * Override to use resources/views instead of views.
     *
     * @return string
     */
    protected function getViewsPath(): string
    {
        return $this->getBasePath() . '/resources/views';
    }

    protected function getConfigFile(): string
    {
        return __DIR__ . '/../config/passwordless.php';
    }
}
