<?php

namespace JWTAuth\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        if (!class_exists('CreateJwtTokensStoreTables')) {
            array_map('unlink', glob(__DIR__ . '/../vendor/orchestra/testbench-core/laravel/database/migrations/*_create_jwt_tokens_store_tables.php'));
            $this->artisan('vendor:publish', [ '--tag' => 'migrations', '--force' => true ]);
        }


        $this->artisan('migrate', [ '--database' => 'testbench' ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            \JWTAuth\ServiceProvider::class,
        ];
    }

    public function defineEnvironment($app)
    {
        $app['config']->set('auth.guards.test_api', [
            'driver'      => 'jwt',
            'provider'    => 'advertisers',
            'public_key'  => 'jwt-keys/jwtRS256.key.pub',
            'private_key' => 'jwt-keys/jwtRS256.key',
            'blocklist'   => 'filesystem',
            'options'     => [],
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
