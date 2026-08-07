<?php

namespace App\Providers;

use App\Services\LiqPay\LiqPayClient;
use App\Services\Telegram\TelegramApi;
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
        //
    }
}
