<!DOCTYPE html>
<html lang="uk">
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
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <a href="{{ route('orders.index') }}" wire:navigate class="text-xl font-bold text-indigo-600">
                        {{ config('app.name') }}
                    </a>

                    <div class="hidden sm:flex items-center gap-6 text-sm font-medium">
                        <a href="{{ route('orders.index') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">Заказы</a>

                        @auth
                            <a href="{{ route('orders.create') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">Опубликовать заказ</a>
                            <a href="{{ route('orders.my') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">Мои заказы</a>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-gray-600 hover:text-indigo-600">Админка</a>
                            @endif
                        @endauth
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('users.show', auth()->user()) }}" wire:navigate class="text-sm text-gray-700 hover:text-indigo-600">{{ auth()->user()->name }}</a>
                            <a href="{{ route('dashboard') }}" wire:navigate class="text-sm text-gray-500 hover:text-indigo-600">Кабинет</a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="text-sm text-gray-600 hover:text-indigo-600">Войти</a>
                            <a href="{{ route('register') }}" wire:navigate class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Регистрация</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{ $slot }}
        </main>
    </body>
</html>
