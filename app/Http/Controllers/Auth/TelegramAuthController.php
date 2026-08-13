<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TelegramLoginToken;
use App\Models\User;
use App\Services\TelegramLoginVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramAuthController extends Controller
{
    /**
     * Handle the callback from the Telegram Login Widget on the site.
     */
    public function widgetCallback(Request $request): RedirectResponse
    {
        $botToken = config('services.telegram.bot_token');

        if (! $botToken || ! TelegramLoginVerifier::verify($request->query(), $botToken)) {
            abort(403, 'Invalid Telegram login payload.');
        }

        $user = $this->findOrCreateFromTelegram(
            telegramId: (int) $request->query('id'),
            username: $request->query('username'),
            firstName: $request->query('first_name'),
            lastName: $request->query('last_name'),
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle a one-time magic-link login initiated from the Telegram bot.
     */
    public function magicLogin(Request $request, string $token): RedirectResponse
    {
        $loginToken = TelegramLoginToken::where('token', $token)->first();

        if (! $loginToken || ! $loginToken->isValid()) {
            abort(403, 'Login link has expired.');
        }

        // Atomic claim: only the request that actually flips used_at from
        // NULL wins, so two concurrent hits on the same link (link preview
        // bots, double-tap) can't both authenticate.
        $claimed = TelegramLoginToken::whereKey($loginToken->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        abort_unless($claimed === 1, 403, 'Login link has expired.');

        Auth::login($loginToken->user, remember: true);

        $to = $request->query('to');

        if (is_string($to) && str_starts_with($to, '/') && ! str_starts_with($to, '//')) {
            return redirect($to);
        }

        return redirect()->intended(route('dashboard'));
    }

    private function findOrCreateFromTelegram(int $telegramId, ?string $username, ?string $firstName, ?string $lastName): User
    {
        $user = User::where('telegram_id', $telegramId)->first();

        if ($user) {
            $user->update(['telegram_username' => $username]);

            return $user;
        }

        return User::create([
            'name' => trim("{$firstName} {$lastName}") ?: ('tg_'.$telegramId),
            'telegram_id' => $telegramId,
            'telegram_username' => $username,
        ]);
    }
}
