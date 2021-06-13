<?php

namespace JWTAuth\Tests;

use Illuminate\Support\Facades\Auth;
use JWTAuth\BlockList\FileJwtBlockList;
use JWTAuth\Contracts\JWTManagerContract;
use JWTAuth\Eloquent\StoredJwtToken;
use JWTAuth\JWTManager;
use JWTAuth\Tests\Fixtures\Models\User;

class JWTManagerTest extends TestCase
{

    /** @test */
    public function manager_implement_contract()
    {
        $manager = new JWTManager();

        $this->assertTrue(is_subclass_of($manager, JWTManagerContract::class));
    }

    /** @test */
    public function auth_return_manager()
    {
        $guard = Auth::guard('test_api');

        $userData = [
            'name'     => 'Test name',
            'email'    => 'test@email.test',
            'password' => bcrypt('pass'),
        ];

        /** @var User $user */
        $user = User::create($userData);

        $this->artisan('jwt:keys:generate --force')
             ->assertExitCode(0);


        $token = $guard->attempt([
            'email'    => $userData['email'],
            'password' => 'pass',
        ]);

        $this->assertTrue(is_string($token));
        $this->assertEquals(1, StoredJwtToken::count());
        $this->assertInstanceOf(User::class, $guard->user());

        $guard->unsetUser();
        $this->assertNull($guard->user());

        $this->assertTrue($guard->validate([
            'email' => $userData['email'],
        ]));

        $this->assertFalse($guard->validate([
            'email' => $userData['email'] . '-s',
        ]));

        $this->assertFalse($guard->validate());

        request()->merge([ 'api_token' => $token ]);

        $this->assertInstanceOf(User::class, $guard->user());
        $this->assertEquals($userData['email'], $guard->user()->email);

        $blocklist = $guard->blockList();
        $this->assertInstanceOf(FileJwtBlockList::class, $blocklist);

        $guard->logout();
        $this->assertNull($guard->user());

        $this->assertTrue($blocklist->isBlockListed($guard->getJWTManager()));
    }
}
