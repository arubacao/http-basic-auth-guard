<?php

/**
 * This file is part of Http Basic Auth Guard.
 *
 * (c) Christopher Lass <arubacao@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Attempting;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthenticationTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->app->config->set('auth.guards', [
            'api' => [
                'driver' => 'basic',
                'provider' => 'users',
            ],
        ]);
        $this->app->config->set('auth.providers', [
            'users' => [
                'driver' => 'eloquent',
                'model'  => User::class,
            ],
        ]);
        $this->app->config->set('auth.defaults', [
            'guard' => 'api',
            'passwords' => 'users',
        ]);
    }


    /** @test */
    public function user_gets_unauthorized_without_credentials_with_auth_middleware()
    {
        $user = factory(User::class)->create();
        $this->app->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this->json('GET', 'api/whatever', [], [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW' => '',
        ])
            ->seeStatusCode(401);
    }

    /** @test */
    public function user_gets_unauthorized_with_wrong_credentials_with_auth_middleware()
    {
        $user = factory(User::class)->create();
        $this->app->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this->json('GET', 'api/whatever', [], [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW' => 'blablablablbalabajbakkskkas',
        ])
            ->seeStatusCode(401);
    }

    /** @test */
    public function user_gets_ok_with_correct_credentials_with_auth_middleware()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('123456'),
        ]);
        $this->app->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this->json('GET', 'api/whatever', [], [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW' => '123456',
        ])
            ->seeStatusCode(200)
            ->assertEquals($user->toArray(), Auth::user()->toArray());
    }

    /** @test */
    public function attempting_event_gets_fired_with_correct_credentials_with_auth_middleware()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('123456'),
        ]);
        $this->app->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this->expectsEvents(Attempting::class)
            ->json('GET', 'api/whatever', [], [
                'PHP_AUTH_USER' => $user->email,
                'PHP_AUTH_PW' => '123456',
            ])
            ->seeStatusCode(200)
            ->assertEquals($user->toArray(), Auth::user()->toArray());
    }

    /** @test */
    public function login_event_not_fired_with_correct_credentials_with_auth_middleware()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('123456'),
        ]);
        $this->app->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this
//            ->doesntExpectEvents(Login::class)
            ->json('GET', 'api/whatever', [], [
                'PHP_AUTH_USER' => $user->email,
                'PHP_AUTH_PW' => '123456',
            ])
            ->seeStatusCode(200)
            ->assertEquals($user->toArray(), Auth::user()->toArray());
    }

    /** @test */
    public function basic_method_fires_attempting_event_with_valid_credentials()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('123456'),
        ]);

        $this->app->get('api/whatever', function () {
            Auth::basic();
        });
        $this->expectsEvents(Attempting::class)
            ->json('GET', 'api/whatever', [], [
                'PHP_AUTH_USER' => $user->email,
                'PHP_AUTH_PW' => '123456',
            ])
            ->assertEquals($user->toArray(), Auth::user()->toArray());
    }

    /** @test */
    public function basic_method_fires_attempting_event_with_invalid_credentials()
    {
        $user = factory(User::class)->create();

        $this->app->get('api/whatever', function () {
            Auth::basic();
        });
        $this->expectsEvents(Attempting::class)
            ->json('GET', 'api/whatever', [], [
                'PHP_AUTH_USER' => $user->email,
                'PHP_AUTH_PW' => '',
            ])
            ->assertNull(Auth::user());
    }

    /** @test */
    public function basic_method_fires_login_event_with_valid_credentials()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('123456'),
        ]);

        $this->app->get('api/whatever', function () {
            Auth::basic();
        });
        $this->expectsEvents(Login::class)
            ->json('GET', 'api/whatever', [], [
                'PHP_AUTH_USER' => $user->email,
                'PHP_AUTH_PW' => '123456',
            ])
            ->assertEquals($user->toArray(), Auth::user()->toArray());
    }

    /** @test */
    public function basic_method_does_not_fire_login_event_with_invalid_credentials()
    {
        $user = factory(User::class)->create();

        $this->app->get('api/whatever', function () {
            Auth::basic();
        });
        $this
//            ->doesntExpectEvents(Login::class)
            ->json('GET', 'api/whatever', [], [
                'PHP_AUTH_USER' => $user->email,
                'PHP_AUTH_PW' => '',
            ])
            ->assertNull(Auth::user());
    }

    /** @test */
    public function onceBasic_method_does_not_fire_login_event_with_valid_credentials()
    {
        $user = factory(User::class)->create([
            'password' => Hash::make('123456'),
        ]);

        $this->app->get('api/whatever', function () {
            Auth::onceBasic();
        });
        $this
//            ->doesntExpectEvents(Login::class)
            ->json('GET', 'api/whatever', [], [
                'PHP_AUTH_USER' => $user->email,
                'PHP_AUTH_PW' => '123456',
            ])
            ->assertEquals($user->toArray(), Auth::user()->toArray());
    }
}
