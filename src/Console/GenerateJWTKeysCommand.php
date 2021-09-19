<?php


namespace JWTAuth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 *
 * Example of manual creation:
 * cd storage/jwt-keys
 * ssh-keygen -t rsa -b 4096 -m PEM -f jwtRS256.key
 * openssl rsa -in jwtRS256.key -pubout -outform PEM -out jwtRS256.key.pub
 *
 * Class GenerateJWTKeysCommand
 * @package JWTAuth\Console
 */
class GenerateJWTKeysCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:keys:generate
    {--key-dir=jwt-keys : Key directory}
    {--key-name=jwtRS256 : Key name}
    {--force : Force precess}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JWT Keys';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $directory = $this->option('key-dir');
        $keyName   = $this->option('key-name');

        if (!$this->option('force')) {
            $directory = $this->ask('Directory name where will be stored keys:', $directory);
            $keyName   = $this->ask('Key name:', $keyName);
        }

        $dirFullPath    = storage_path($directory);
        $privateKeyPath = storage_path("{$directory}/{$keyName}.key");
        $publicKeyPath  = storage_path("{$directory}/{$keyName}.key.pub");

        if (!$this->newFilesCanBeStored($privateKeyPath, $publicKeyPath)) {
            return 1;
        }

        $this->prepareDirectoryForFiles($dirFullPath);

        if (!$this->createPrivateKeyFile($privateKeyPath)) {
            return 2;
        }

        if (!$this->createPublicKeyFile($privateKeyPath, $publicKeyPath)) {
            return 3;
        }

        return 0;
    }

    /**
     * Create and store private key file.
     *
     * @param string $privateKeyPath
     *
     * @return bool
     */
    protected function createPrivateKeyFile(string $privateKeyPath): bool
    {
        $process = new Process([
            'ssh-keygen',
            '-t',
            'rsa',
            '-b',
            '4096',
            '-N',
            '',
            '-q',
            '-m',
            'PEM',
            '-f',
            $privateKeyPath,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error($process->getErrorOutput());
            $this->error('Private key not created.');

            return false;
        }

        return true;
    }

    /**
     * Create and store public key file.
     *
     * @param string $privateKeyPath
     * @param string $publicKeyPath
     *
     * @return bool
     */
    protected function createPublicKeyFile(string $privateKeyPath, string $publicKeyPath): bool
    {
        $process = new Process([
            'openssl',
            'rsa',
            '-in',
            $privateKeyPath,
            '-pubout',
            '-outform',
            'PEM',
            '-out',
            $publicKeyPath,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error($process->getErrorOutput());
            $this->error('Public key not converted.');

            return false;
        }

        return true;
    }

    /**
     * @param string $dirFullPath
     */
    protected function prepareDirectoryForFiles(string $dirFullPath)
    {
        if (!File::isDirectory($dirFullPath)) {
            File::makeDirectory($dirFullPath, 0755, true, true);
        }
        if (!File::exists("{$dirFullPath}/.gitignore")) {
            File::put("{$dirFullPath}/.gitignore", "*\n!.gitignore");
        }
    }

    /**
     * @param string $privateKeyPath
     * @param string $publicKeyPath
     *
     * @return bool
     */
    protected function newFilesCanBeStored(string $privateKeyPath, string $publicKeyPath): bool
    {
        if (File::exists($privateKeyPath) || File::exists($publicKeyPath)) {
            if (File::exists($privateKeyPath)) {
                $this->warn("File exists: {$privateKeyPath}");
            }
            if (File::exists($publicKeyPath)) {
                $this->warn("File exists: {$publicKeyPath}");
            }
            if (!$this->option('force') && !$this->confirm('Keys already exists. Do you wish to continue?')) {
                return false;
            }
            File::delete($privateKeyPath);
            File::delete($publicKeyPath);
        }

        return true;
    }
}
