<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramApi;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook';

    protected $description = 'Register the application webhook URL with Telegram';

    public function handle(TelegramApi $api): int
    {
        $secret = config('services.telegram.webhook_secret');
        $url = rtrim(config('app.url'), '/')."/telegram/webhook/{$secret}";

        $result = $api->setWebhook($url, $secret);

        $this->info($result['ok'] ?? false ? "Webhook set to {$url}" : 'Failed to set webhook.');
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $result['ok'] ?? false ? self::SUCCESS : self::FAILURE;
    }
}
