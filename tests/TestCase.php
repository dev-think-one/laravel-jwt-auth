<?php

namespace JWTAuth\Tests;

use Illuminate\Support\Facades\File;
use JWTAuth\Tests\Fixtures\Models\User;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        if (!class_exists('CreateJwtTokensStoreTables')) {
            array_map('unlink', glob(__DIR__ . '/../vendor/orchestra/testbench-core/laravel/database/migrations/*.php'));
            $this->artisan('vendor:publish', [ '--tag' => 'migrations', '--force' => true ]);
            array_map(function ($f) {
                File::copy($f, __DIR__ . '/../vendor/orchestra/testbench-core/laravel/database/migrations/' . basename($f));
            }, glob(__DIR__ . '/Fixtures/migrations/*.php'));
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

        $app['config']->set('auth.guards.test_api', [
            'driver'      => 'jwt',
            'provider'    => 'users',
            'public_key'  => 'jwt-keys/jwtRS256.key.pub',
            'private_key' => 'jwt-keys/jwtRS256.key',
            'blocklist'   => 'filesystem',
            'options'     => [],
        ]);
        $app['config']->set('auth.providers.users.model', User::class);
    }
}
