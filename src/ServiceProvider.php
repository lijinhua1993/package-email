<?php

namespace Lijinhua\Email;

use Illuminate\Contracts\Support\DeferrableProvider;
use Lijinhua\Email\Storage\CacheStorage;

class ServiceProvider extends \Illuminate\Support\ServiceProvider implements DeferrableProvider
{

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('email.php'),
            ]);

            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'email'
        );

        $this->app->singleton(Email::class, function ($app) {
            $storage = config('email.storage', CacheStorage::class);

            return new Email(new $storage());
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [Email::class];
    }
}