<?php


namespace JWTAuth\Tests\Console;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use JWTAuth\Tests\TestCase;

class PruneTokensStorageCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function success_return()
    {
        $table = DB::table(config('jwt-auth.tables.tokens'));

        $table->insert([
            'id'             => 1,
            'tokenable_type' => '\Some\Class',
            'tokenable_id'   => 1,
            'name'           => 'jwt',
            'jti'            => uniqid(),
            'exp'            => Carbon::now()->addHour()->timestamp,
            'abilities'      => '["*"]',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),

        ]);
        $table->insert([
            'id'             => 2,
            'tokenable_type' => '\Some\Class',
            'tokenable_id'   => 2,
            'name'           => 'jwt',
            'jti'            => uniqid(),
            'exp'            => Carbon::now()->addHour()->timestamp,
            'abilities'      => '["*"]',
            'created_at'     => Carbon::now()->subDays(31),
            'updated_at'     => Carbon::now(),

        ]);
        $table->insert([
            'id'             => 3,
            'tokenable_type' => '\Some\Class',
            'tokenable_id'   => 3,
            'name'           => 'jwt',
            'jti'            => uniqid(),
            'exp'            => Carbon::now()->addHour()->timestamp,
            'abilities'      => '["*"]',
            'created_at'     => Carbon::now()->subDays(16),
            'updated_at'     => Carbon::now(),

        ]);

        $this->assertEquals(3, $table->count());

        $this->artisan('jwt:storage:prune')->assertExitCode(0);

        $this->assertEquals(2, $table->count());
    }

    /** @test */
    public function success_return_with_days_count()
    {
        $table = DB::table(config('jwt-auth.tables.tokens'));

        $table->insert([
            'id'             => 1,
            'tokenable_type' => '\Some\Class',
            'tokenable_id'   => 1,
            'name'           => 'jwt',
            'jti'            => uniqid(),
            'exp'            => Carbon::now()->addHour()->timestamp,
            'abilities'      => '["*"]',
            'created_at'     => Carbon::now(),
            'updated_at'     => Carbon::now(),

        ]);
        $table->insert([
            'id'             => 2,
            'tokenable_type' => '\Some\Class',
            'tokenable_id'   => 2,
            'name'           => 'jwt',
            'jti'            => uniqid(),
            'exp'            => Carbon::now()->addHour()->timestamp,
            'abilities'      => '["*"]',
            'created_at'     => Carbon::now()->subDays(31),
            'updated_at'     => Carbon::now(),

        ]);
        $table->insert([
            'id'             => 3,
            'tokenable_type' => '\Some\Class',
            'tokenable_id'   => 3,
            'name'           => 'jwt',
            'jti'            => uniqid(),
            'exp'            => Carbon::now()->addHour()->timestamp,
            'abilities'      => '["*"]',
            'created_at'     => Carbon::now()->subDays(16),
            'updated_at'     => Carbon::now(),

        ]);

        $this->assertEquals(3, $table->count());

        $this->artisan('jwt:storage:prune --days=15')->assertExitCode(0);

        $this->assertEquals(1, $table->count());
    }



    /** @test */
    public function error_if_not_valid_db_return()
    {
        app()['config']->set('jwt-auth.tables.tokens', 'test_test_table');
        $this->expectExceptionMessage('General error: 1 no such table: test_test_table');
        $this->artisan('jwt:storage:prune');
    }
}
