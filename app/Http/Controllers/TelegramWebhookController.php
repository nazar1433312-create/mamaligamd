<?php

namespace App\Http\Controllers;

use App\Services\Telegram\BotHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, string $secret, BotHandler $bot): Response
    {
        if (! hash_equals((string) config('services.telegram.webhook_secret'), $secret)) {
            return response()->noContent(ResponseCode::HTTP_FORBIDDEN);
        }

        $update = $request->all();

        try {
            $bot->handleUpdate($update);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->noContent();
    }
}
