<?php


namespace JWTAuth\Console;

use Illuminate\Console\Command;
use JWTAuth\Contracts\HasObsoleteRecords;

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
    protected $signature = 'jwt:block-list:prune {blockList : block list class}';

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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle(): int
    {
        $class     = $this->argument('blockList');
        $blockList = app()->make($class);

        if ($blockList instanceof HasObsoleteRecords) {
            if ($blockList->removeObsoleteRecords()) {
                $this->info('Pruned');

                return 0;
            }

            $this->warn('Prune error');

            return 1;
        }

        $this->warn('Blocklist has not interface "HasOutdatedRecords"');

        return 0;
    }
}
