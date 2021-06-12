<?php

namespace JWTAuth\Tests;

use JWTAuth\Contracts\JWTManagerContract;
use JWTAuth\JWTManager;

class JWTManagerTest extends TestCase
{

    /** @test */
    public function manager_implement_contract()
    {
        $manager = new JWTManager();

        $this->assertTrue(is_subclass_of($manager, JWTManagerContract::class));
    }
}
