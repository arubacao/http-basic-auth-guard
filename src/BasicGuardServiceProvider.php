<?php

namespace Arubacao\BasicAuth;

use Illuminate\Support\ServiceProvider;

class BasicGuardServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->extend('basic', function ($app, $name, array $config) {
            $guard = new BasicGuard(
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );

            $app->refresh('request', $guard, 'setRequest');

            // Return an instance of Illuminate\Contracts\Auth\Guard...
            return $guard;
        });
    }
}
