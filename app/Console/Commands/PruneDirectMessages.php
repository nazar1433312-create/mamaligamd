<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class PruneDirectMessages extends Command
{
    protected $signature = 'messages:prune {--days=30}';

    protected $description = 'Delete direct (non-order) chat messages older than N days to keep the messenger tidy';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = Message::whereNull('order_id')
            ->where('created_at', '<', now()->subDays($days))
            ->whereHas('sender', fn ($q) => $q->whereNull('history_paid_at'))
            ->whereHas('recipient', fn ($q) => $q->whereNull('history_paid_at'))
            ->delete();

        $this->info("Deleted {$deleted} direct messages older than {$days} days.");

        return self::SUCCESS;
    }
}
