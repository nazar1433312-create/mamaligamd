<?php

namespace App\Services\Support;

use App\Models\SupportTicket;
use App\Services\Telegram\TelegramApi;

class SupportNotifier
{
    public function __construct(
        private readonly TelegramApi $api
    ) {}

    public function notifyNewTicket(SupportTicket $ticket, string $firstMessage): void
    {
        $chatId = config('services.telegram.admin_chat_id');

        if (! $chatId) {
            return;
        }

        $text = "🆘 <b>Новое обращение #{$ticket->id}</b>\n"
            ."От: {$ticket->contactLabel()}"
            .($ticket->guest_contact ? " ({$ticket->guest_contact})" : '')
            ."\nТема: {$ticket->subject}\n\n{$firstMessage}\n\n"
            ."Ответьте на это сообщение в Telegram, чтобы ответить пользователю.";

        $response = $this->api->sendMessage($chatId, $text);

        if ($response['ok'] ?? false) {
            $ticket->update([
                'telegram_notify_chat_id' => $chatId,
                'telegram_notify_message_id' => $response['result']['message_id'],
            ]);
        }
    }

    public function notifyNewMessage(SupportTicket $ticket, string $message): void
    {
        $chatId = config('services.telegram.admin_chat_id');

        if (! $chatId) {
            return;
        }

        $text = "💬 <b>Ответ по обращению #{$ticket->id}</b> ({$ticket->contactLabel()})\n\n{$message}";

        $response = $this->api->sendMessage($chatId, $text);

        if ($response['ok'] ?? false) {
            $ticket->update([
                'telegram_notify_chat_id' => $chatId,
                'telegram_notify_message_id' => $response['result']['message_id'],
            ]);
        }
    }
}
