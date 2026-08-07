<?php

namespace App\Services\Telegram;

use App\Models\User;

class UserNotifier
{
    public function __construct(
        private readonly TelegramApi $api
    ) {}

    public function notify(?User $user, string $text): void
    {
        if ($user?->telegram_id) {
            $this->api->sendMessage($user->telegram_id, $text);
        }
    }
}
