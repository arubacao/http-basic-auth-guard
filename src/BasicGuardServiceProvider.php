<?php

/**
 * This file is part of Http Basic Auth Guard.
 *
 * (c) Christopher Lass <arubacao@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
                $name,
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }

            // Return an instance of Illuminate\Contracts\Auth\Guard...
            return $guard;
        });
    }
}
