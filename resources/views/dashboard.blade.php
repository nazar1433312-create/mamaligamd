<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-x-4">
                    <a href="{{ route('orders.index') }}" wire:navigate class="text-indigo-600 hover:underline">Заказы →</a>
                    <a href="{{ route('orders.my') }}" wire:navigate class="text-indigo-600 hover:underline">Мои заказы →</a>
                    <a href="{{ route('orders.create') }}" wire:navigate class="text-indigo-600 hover:underline">Опубликовать заказ →</a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-indigo-600 hover:underline">Админка →</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
