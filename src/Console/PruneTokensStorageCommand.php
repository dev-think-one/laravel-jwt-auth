<?php


namespace JWTAuth\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use JWTAuth\Eloquent\StoredJwtToken;

class PruneTokensStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:storage:prune {--days=30 : The number of days to retain StoredJwtToken data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune stale entries from the StoredJwtToken table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $deleted = StoredJwtToken::query()
            ->where('created_at', '<', Carbon::now()->subDays($this->option('days')))
            ->delete();
        if ($deleted) {
            $this->info('Stored tokens pruned.');
        }

        return 0;
    }
}
