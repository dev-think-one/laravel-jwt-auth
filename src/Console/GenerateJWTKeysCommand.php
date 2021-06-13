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

        if (File::exists($privateKeyPath) || File::exists($publicKeyPath)) {
            if (File::exists($privateKeyPath)) {
                $this->warn("File exists: {$privateKeyPath}");
            }
            if (File::exists($publicKeyPath)) {
                $this->warn("File exists: {$publicKeyPath}");
            }
            if (!$this->option('force') && !$this->confirm('Keys already exists. Do you wish to continue?')) {
                return 1;
            }
            File::delete($privateKeyPath);
            File::delete($publicKeyPath);
        }

        if (!File::isDirectory($dirFullPath)) {
            File::makeDirectory($dirFullPath, 0755, true, true);
        }
        if (!File::exists("{$dirFullPath}/.gitignore")) {
            File::put("{$dirFullPath}/.gitignore", "*\n!.gitignore");
        }

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

            return 2;
        }

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

            return 3;
        }

        return 0;
    }
}
