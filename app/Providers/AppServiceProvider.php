<?php

namespace App\Providers;

use App\Services\LiqPay\LiqPayClient;
use App\Services\Telegram\TelegramApi;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TelegramApi::class, fn () => new TelegramApi(
            (string) config('services.telegram.bot_token')
        ));

        $this->app->singleton(LiqPayClient::class, fn () => new LiqPayClient(
            (string) config('services.liqpay.public_key'),
            (string) config('services.liqpay.private_key'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // We always run behind an HTTPS-terminating reverse proxy; PHP itself
        // only ever sees plain HTTP, so force https for all generated URLs
        // instead of trusting forwarded headers (which trips a Symfony bug
        // under our Docker NAT setup).
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
