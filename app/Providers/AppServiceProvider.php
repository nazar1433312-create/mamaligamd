<?php

namespace App\Providers;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
