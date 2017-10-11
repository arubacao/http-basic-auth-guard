<?php

/**
 * This file is part of Http Basic Auth Guard.
 *
 * (c) Christopher Lass <arubacao@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Arubacao\BasicAuth\BasicGuardServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GrahamCampbell\TestBench\AbstractPackageTestCase;

abstract class AbstractTestCase extends AbstractPackageTestCase
{
    use RefreshDatabase;

    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return string
     */
    protected function getServiceProviderClass($app)
    {
        return BasicGuardServiceProvider::class;
    }

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $app->config->set('database.default', 'testbench');
        $app->config->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $this->withFactories(realpath(__DIR__.'/database/factories'));
    }

    /**
     * Setup the application environment.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->config->set('auth.guards', [
            'api' => [
                'driver' => 'basic',
                'provider' => 'users',
            ],
        ]);
        $app->config->set('auth.providers', [
            'users' => [
                'driver' => 'eloquent',
                'model'  => User::class,
            ],
        ]);
        $app->config->set('auth.defaults', [
            'guard' => 'api',
            'passwords' => 'users',
        ]);
    }
}
