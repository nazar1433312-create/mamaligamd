<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramApi
{
    public function __construct(
        private readonly string $token
    ) {}

    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): array
    {
        return $this->call('sendMessage', array_filter([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
        ], fn ($v) => ! is_null($v)));
    }

    public function editMessageText(int|string $chatId, int $messageId, string $text, ?array $replyMarkup = null): array
    {
        return $this->call('editMessageText', array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => $replyMarkup ? json_encode($replyMarkup) : null,
        ], fn ($v) => ! is_null($v)));
    }

    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null): array
    {
        return $this->call('answerCallbackQuery', array_filter([
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]));
    }

    public function setWebhook(string $url, string $secretToken): array
    {
        return $this->call('setWebhook', [
            'url' => $url,
            'secret_token' => $secretToken,
        ]);
    }

    public function deleteWebhook(): array
    {
        return $this->call('deleteWebhook');
    }

    public function getWebhookInfo(): array
    {
        return $this->call('getWebhookInfo');
    }

    private function call(string $method, array $params = []): array
    {
        $response = Http::asForm()
            ->timeout(10)
            ->post("https://api.telegram.org/bot{$this->token}/{$method}", $params);

        return $response->json() ?? [];
    }
}
