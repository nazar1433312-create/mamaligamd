<?php

namespace App\Console\Commands;

use App\Models\Call;
use Illuminate\Console\Command;

class PruneCalls extends Command
{
    protected $signature = 'calls:prune {--days=30}';

    protected $description = 'Delete call history older than N days to keep the messenger tidy';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = Call::where('created_at', '<', now()->subDays($days))
            ->whereHas('caller', fn ($q) => $q->whereNull('history_paid_at'))
            ->whereHas('callee', fn ($q) => $q->whereNull('history_paid_at'))
            ->delete();

        $this->info("Deleted {$deleted} calls older than {$days} days.");

        return self::SUCCESS;
    }
}
