<?php


namespace JWTAuth\Tests\Console;

use Illuminate\Support\Facades\Auth;
use JWTAuth\BlockList\FileJwtBlockList;
use JWTAuth\Contracts\JwtBlockListContract;
use JWTAuth\JWTGuard;
use JWTAuth\Tests\TestCase;

class PruneBlockListCommandTest extends TestCase
{

    /** @test */
    public function command_has_required_param_guard()
    {
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "guard").');
        $this->artisan('jwt:block-list:prune');
    }

    /** @test */
    public function error_if_guard_not_exists()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Auth guard [api_not_exists] is not defined.');
        $this->artisan('jwt:block-list:prune api_not_exists');
    }

    /** @test */
    public function success_if_does_not_instance_of_HasObsoleteRecords()
    {
        $mockGuard = \Mockery::mock(JWTGuard::class);
        $mockGuard->shouldReceive('blockList')
                  ->andReturn(\Mockery::mock(JwtBlockListContract::class));

        Auth::shouldReceive('guard')
            ->with('test_api')
            ->andReturn($mockGuard);

        $this->artisan('jwt:block-list:prune test_api')
             ->expectsOutput('Blocklist has not interface "HasOutdatedRecords"')
             ->assertExitCode(0);
    }

    /** @test */
    public function success_if_instance_of_HasObsoleteRecords()
    {
        $mockGuard     = \Mockery::mock(JWTGuard::class);
        $mockBlocklist = \Mockery::mock(FileJwtBlockList::class);


        $mockGuard->shouldReceive('blockList')
                  ->andReturn($mockBlocklist);


        $mockBlocklist->shouldReceive('removeObsoleteRecords')
                      ->once()->andReturn(true);
        $mockBlocklist->shouldReceive('removeObsoleteRecords')
                      ->once()->andReturn(false);

        Auth::shouldReceive('guard')
            ->with('test_api')
            ->andReturn($mockGuard);

        $this->artisan('jwt:block-list:prune test_api')
             ->expectsOutput('Pruned')
             ->assertExitCode(0);

        $this->artisan('jwt:block-list:prune test_api')
             ->expectsOutput('Prune error')
             ->assertExitCode(1);
    }
}
