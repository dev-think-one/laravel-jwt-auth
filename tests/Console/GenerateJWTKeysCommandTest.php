<?php


namespace JWTAuth\Tests\Console;

use Illuminate\Support\Facades\File;
use JWTAuth\Tests\TestCase;

class GenerateJWTKeysCommandTest extends TestCase
{

    /** @test */
    public function command_pass()
    {
        $directory = 'test_dir';
        $keyName   = 'testKey';
        File::deleteDirectory(storage_path($directory));

        $this->artisan('jwt:keys:generate')
             ->expectsQuestion('Directory name where will be stored keys:', 'test_dir')
             ->expectsQuestion('Key name:', $keyName)
             ->assertExitCode(0);

        $this->assertFileExists(storage_path("{$directory}/.gitignore"));
        $this->assertFileExists(storage_path("{$directory}/{$keyName}.key"));
        $this->assertFileExists(storage_path("{$directory}/{$keyName}.key.pub"));
    }

    /** @test */
    public function command_check_before_override_and_user_can_skip()
    {
        $directory      = 'test_dir';
        $keyName        = 'testKey';
        $privateKeyPath = storage_path("{$directory}/{$keyName}.key");
        $publicKeyPath  = storage_path("{$directory}/{$keyName}.key.pub");
        File::deleteDirectory(storage_path($directory));
        File::ensureDirectoryExists(storage_path($directory));
        File::put($privateKeyPath, 'test content');

        $this->artisan('jwt:keys:generate')
             ->expectsQuestion('Directory name where will be stored keys:', 'test_dir')
             ->expectsQuestion('Key name:', $keyName)
             ->expectsOutput("File exists: {$privateKeyPath}")
             ->expectsQuestion('Keys already exists. Do you wish to continue?', false)
             ->assertExitCode(1);

        $this->assertFileDoesNotExist(storage_path("{$directory}/.gitignore"));
        $this->assertFileExists($privateKeyPath);
        $this->assertFileDoesNotExist($publicKeyPath);


        File::put($publicKeyPath, 'test content');

        $this->artisan('jwt:keys:generate')
             ->expectsQuestion('Directory name where will be stored keys:', 'test_dir')
             ->expectsQuestion('Key name:', $keyName)
             ->expectsOutput("File exists: {$privateKeyPath}")
             ->expectsOutput("File exists: {$publicKeyPath}")
             ->expectsQuestion('Keys already exists. Do you wish to continue?', false)
             ->assertExitCode(1);

        $this->assertFileDoesNotExist(storage_path("{$directory}/.gitignore"));
        $this->assertFileExists($privateKeyPath);
        $this->assertFileExists($publicKeyPath);
    }

    /** @test */
    public function command_check_before_override_and_user_can_continue()
    {
        $directory      = 'test_dir';
        $keyName        = 'testKey';
        $privateKeyPath = storage_path("{$directory}/{$keyName}.key");
        $publicKeyPath  = storage_path("{$directory}/{$keyName}.key.pub");
        File::deleteDirectory(storage_path($directory));
        File::ensureDirectoryExists(storage_path($directory));
        File::put($privateKeyPath, 'test content');
        File::put($publicKeyPath, 'test content');


        $this->artisan('jwt:keys:generate')
             ->expectsQuestion('Directory name where will be stored keys:', 'test_dir')
             ->expectsQuestion('Key name:', $keyName)
             ->expectsOutput("File exists: {$privateKeyPath}")
             ->expectsOutput("File exists: {$publicKeyPath}")
             ->expectsQuestion('Keys already exists. Do you wish to continue?', true)
             ->assertExitCode(0);

        $this->assertFileExists(storage_path("{$directory}/.gitignore"));
        $this->assertFileExists($privateKeyPath);
        $this->assertFileExists($publicKeyPath);
    }

    /** @test */
    public function check_is_process_successful()
    {
        $directory      = 'test_dir';
        $keyName        = 'testKey';
        $privateKeyPath = storage_path("{$directory}/{$keyName}.key");
        // $publicKeyPath  = storage_path( "{$directory}/{$keyName}.key.pub" );
        File::deleteDirectory(storage_path($directory));
        File::ensureDirectoryExists(storage_path($directory));
        File::put($privateKeyPath, 'test content');

        File::shouldReceive('exists')->twice()->andReturn(false);
        File::shouldReceive('isDirectory')->once()->andReturn(true);
        File::shouldReceive('exists')->once()->andReturn(true);

        $this->artisan('jwt:keys:generate')
             ->expectsQuestion('Directory name where will be stored keys:', 'test_dir')
             ->expectsQuestion('Key name:', $keyName)
             ->expectsOutput('Private key not created.')
            ->assertExitCode(2);
    }
}
