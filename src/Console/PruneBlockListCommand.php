<?php


namespace JWTAuth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use JWTAuth\Contracts\HasObsoleteRecords;
use JWTAuth\JWTGuard;

/**
 * Class PruneBlockListCommand
 *
 * @example phpartisan jwt:block-list:prune "\JWTAuth\BlockList\FileJwtBlockList"
 */
class PruneBlockListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:block-list:prune {guard : Auth guard}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune blocklist';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $guardName = $this->argument('guard');
        if (!is_string($guardName) && !is_null($guardName)) {
            throw new InvalidArgumentException('Not valid "guard" parameter.');
        }

        /** @var JWTGuard $guard */
        $guard     = Auth::guard($guardName);
        $blockList = $guard->blockList();

        if ($blockList instanceof HasObsoleteRecords) {
            if ($blockList->removeObsoleteRecords()) {
                $this->info('Pruned');

                return 0;
            }

            $this->error('Prune error');

            return 1;
        }

        $this->warn('Blocklist has not interface "HasOutdatedRecords"');

        return 0;
    }
}
