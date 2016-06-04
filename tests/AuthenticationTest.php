<?php
/**
 * This file is part of Http Basic Auth Guard.
 *
 * (c) Christopher Lass <arubacao@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Arubacao\Tests\BasicAuth;

class AuthenticationTest extends AbstractTestCase
{
    use \Laravel\Lumen\Testing\DatabaseTransactions;
    use \Laravel\Lumen\Testing\DatabaseMigrations;

    /** @test */
    public function user_gets_unauthorized_without_credentials()
    {
        $user = factory(\App\User::class)->create();
        $this->app->router->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this->json('GET', 'api/whatever', [], [
            'PHP_AUTH_USER' => $user->email,
        ])
            ->seeStatusCode(401);
    }

    /** @test */
    public function user_gets_unauthorized_with_wrong_credentials()
    {
        $user = factory(\App\User::class)->create();
        $this->app->router->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this->json('GET', 'api/whatever', [], [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW' => 'blablablablbalabajbakkskkas',
        ])
            ->seeStatusCode(401);
    }

    /** @test */
    public function user_gets_ok_with_correct_credentials()
    {
        $user = factory(\App\User::class)->create([
            'password' => bcrypt('123456')
        ]);
        $this->app->router->get('api/whatever', ['middleware' => 'auth:api', function () {
            return 'YoYo!';
        }]);
        $this->json('GET', 'api/whatever', [], [
            'PHP_AUTH_USER' => $user->email,
            'PHP_AUTH_PW' => '123456'
        ])
            ->seeStatusCode(200);
    }
}
