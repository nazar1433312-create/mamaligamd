<?php

namespace App\Services;

class TelegramLoginVerifier
{
    /**
     * Verify the payload produced by the Telegram Login Widget.
     *
     * @see https://core.telegram.org/widgets/login#checking-authorization
     */
    public static function verify(array $data, string $botToken, int $maxAgeSeconds = 86400): bool
    {
        if (empty($data['hash']) || empty($data['auth_date'])) {
            return false;
        }

        if (now()->timestamp - (int) $data['auth_date'] > $maxAgeSeconds) {
            return false;
        }

        $hash = $data['hash'];
        $checkData = $data;
        unset($checkData['hash']);

        ksort($checkData);

        $dataCheckString = collect($checkData)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $computedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($computedHash, $hash);
    }
}
