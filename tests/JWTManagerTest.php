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


        /** @var JWTManager $manager */
        $manager   = $guard->getJWTManager();
        $blocklist = $guard->blockList();
        $this->assertInstanceOf(FileJwtBlockList::class, $blocklist);

        $this->assertFalse($blocklist->isBlockListed($manager));

        $guard->logout();
        $this->assertNull($guard->user());
        $this->assertTrue($blocklist->isBlockListed($manager));
    }

    /** @test */
    public function invalid_payload()
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

        request()->merge([ 'api_token' => $token ]);

        /** @var JWTManager $manager */
        $manager   = $guard->getJWTManager();
        $blocklist = $guard->blockList();

        $this->assertFalse($blocklist->isBlockListed($manager));

        $oldPayload = $manager->payload();
        $newPayload = (clone $oldPayload)->add('exp', 0);
        $this->assertFalse($newPayload->isValid());

        $manager->setPayload($newPayload);
        $this->assertTrue($blocklist->isBlockListed($manager));

    }

    /** @test */
    public function token_info()
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

        request()->merge([ 'api_token' => $token ]);

        /** @var JWTManager $manager */
        $manager = $guard->getJWTManager();

        $this->assertIsArray($manager->toArray());
        $this->assertIsString($manager->toJson());
        $this->assertIsArray($manager->jsonSerialize());

        $this->assertEquals(json_encode($manager->toArray()), $manager->toJson());
        $this->assertEquals(json_encode($manager->toArray()), json_encode($manager->jsonSerialize()));
    }

    /** @test */
    public function reencode_token_after_set_payload()
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

        request()->merge([ 'api_token' => $token ]);

        /** @var JWTManager $manager */
        $manager = $guard->getJWTManager();

        $oldToken = $manager->getToken();

        $oldPayload = $manager->payload();
        $manager->setPayload(array_merge($oldPayload->toArray(), [
            'foo' => 'bar',
        ]));

        $this->assertNotEquals($oldToken, $manager->getToken());
    }
}
