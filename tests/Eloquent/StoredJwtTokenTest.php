<?php

namespace JWTAuth\Tests\Eloquent;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use JWTAuth\Eloquent\StoredJwtToken;
use JWTAuth\Tests\Fixtures\Models\User;
use JWTAuth\Tests\TestCase;

class StoredJwtTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function success_return()
    {
        $table = DB::table(config('jwt-auth.tables.tokens'));

        $userData = [
            'name'     => 'Test name',
            'email'    => 'test@email.test',
            'password' => bcrypt('pass'),
        ];

        /** @var User $user */
        $user = User::create($userData);
        $table->insert([
            'id'             => 1,
            'tokenable_type' => $user->getMorphClass(),
            'tokenable_id'   => $user->getKey(),
            'name'           => 'jwt',
            'jti'            => uniqid(),
            'exp'            => Carbon::now()->addHour()->timestamp,
            'abilities'      => '["*"]',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),

        ]);

        $this->assertEquals(1, $table->count());

        /** @var StoredJwtToken $storedToken */
        $storedToken = StoredJwtToken::first();

        $this->assertInstanceOf(StoredJwtToken::class, $storedToken);
        $this->assertInstanceOf(User::class, $storedToken->tokenable);

        $this->assertEquals($userData['email'], $storedToken->tokenable->email);
    }
}
