<?php

namespace JWTAuth\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();
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
}
