<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <nav class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <a href="{{ route('orders.index') }}" wire:navigate class="flex items-center gap-2 text-xl font-extrabold text-indigo-700">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-sm">M</span>
                        {{ config('app.name') }}
                    </a>

                    <div class="hidden sm:flex items-center gap-6 text-sm font-medium">
                        <a href="{{ route('orders.index') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">{{ __('Заказы') }}</a>
                        <a href="{{ route('users.directory') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">{{ __('Специалисты') }}</a>

                        @auth
                            <a href="{{ route('orders.create') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">{{ __('Опубликовать заказ') }}</a>
                            <a href="{{ route('orders.my') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">{{ __('Мои заказы') }}</a>
                        @endauth
                        <a href="{{ route('support.create') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">{{ __('Поддержка') }}</a>
                        <a href="{{ route('game') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">🎮 {{ __('Игра') }}</a>
                        @auth
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">{{ __('Админка') }}</a>
                            @endif
                        @endauth
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            <div class="hidden sm:flex items-center bg-gray-100 rounded-md p-0.5 text-xs font-medium">
                                <a href="{{ route('orders.my') }}" wire:navigate
                                   class="px-2.5 py-1 rounded {{ request()->routeIs('orders.my') ? 'bg-white shadow text-indigo-700' : 'text-gray-500' }}">
                                    🧑‍💼 {{ __('Заказчик') }}
                                </a>
                                <a href="{{ route('executor.dashboard') }}" wire:navigate
                                   class="px-2.5 py-1 rounded {{ request()->routeIs('executor.dashboard') ? 'bg-white shadow text-indigo-700' : 'text-gray-500' }}">
                                    🔧 {{ __('Исполнитель') }}
                                </a>
                            </div>

                            <a href="{{ route('messages.inbox') }}" wire:navigate title="{{ __('Сообщения') }}"
                               class="text-gray-500 hover:text-indigo-600 {{ request()->routeIs('messages.*') ? 'text-indigo-700' : '' }}">
                                💬
                            </a>

                            <a href="{{ route('orders.favorites') }}" wire:navigate title="{{ __('Избранное') }}"
                               class="text-gray-500 hover:text-amber-500 {{ request()->routeIs('orders.favorites') ? 'text-amber-500' : '' }}">
                                ★
                            </a>
                        @endauth

                        <x-language-switcher />

                        @auth
                            <a href="{{ route('users.show', auth()->user()) }}" wire:navigate class="hidden sm:inline-flex items-center gap-1 text-sm text-gray-700 hover:text-indigo-600">
                                {{ auth()->user()->name }}
                                @if(auth()->user()->is_verified) <x-verified-badge /> @endif
                            </a>
                            @unless(auth()->user()->is_verified)
                                <a href="{{ route('settings.verification') }}" wire:navigate class="hidden sm:inline text-sm text-amber-600 hover:text-amber-700">{{ __('Верификация') }}</a>
                            @endunless
                            <a href="{{ route('settings.payout') }}" wire:navigate class="hidden sm:inline text-sm text-gray-500 hover:text-indigo-600">{{ __('Выплаты') }}</a>
                            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm text-gray-500 hover:text-indigo-600">{{ __('Кабинет') }}</a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm text-gray-600 hover:text-indigo-600">{{ __('Войти') }}</a>
                            <a href="{{ route('register') }}" wire:navigate class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">{{ __('Регистрация') }}</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>

        @auth
            @persist('call-widget')
                <x-call-widget />
            @endpersist
        @endauth

        <footer class="mt-16 border-t border-gray-200 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <a href="https://t.me/DeliveryMDBOTBOT" target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-800 text-white text-sm font-medium px-4 py-3 rounded-lg hover:opacity-90 transition">
                    🚀 {{ __('Доставка по Молдове — попробуй нашего Telegram-бота') }} @DeliveryMDBOTBOT →
                </a>
            </div>
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-sm text-gray-500 flex flex-col sm:flex-row justify-between gap-4">
                <span>© {{ date('Y') }} {{ config('app.name') }} — {{ __('услуги по всей Молдове') }}</span>
                <div class="flex gap-4">
                    <a href="{{ route('about') }}" wire:navigate class="hover:text-indigo-600">{{ __('О нас') }}</a>
                    <a href="{{ route('support.create') }}" wire:navigate class="hover:text-indigo-600">{{ __('Поддержка') }}</a>
                </div>
            </div>
        </footer>
    </body>
</html>
