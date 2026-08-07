<div>
    <h1 class="text-2xl font-bold mb-6">★ {{ __('Избранное') }}</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($orders as $order)
            <div class="relative bg-white p-5 rounded-xl border border-gray-200 hover:border-indigo-400 hover:shadow-md transition">
                <a href="{{ route('orders.show', $order) }}" wire:navigate class="absolute inset-0 z-0" aria-label="{{ $order->title }}"></a>

                <div class="relative z-10 flex items-start justify-between mb-1 pointer-events-none">
                    <span class="text-xs text-indigo-600 font-medium">{{ $order->category->name }}</span>
                    <div class="pointer-events-auto">
                        <livewire:orders.favorite-button :order="$order" :key="'fav-'.$order->id" />
                    </div>
                </div>

                <h3 class="relative z-10 font-semibold text-gray-900 mb-2 line-clamp-2 pointer-events-none">{{ $order->title }}</h3>
                <p class="relative z-10 text-sm text-gray-500 mb-3 line-clamp-2 pointer-events-none">{{ $order->description }}</p>
                <div class="relative z-10 flex items-center justify-between text-sm pointer-events-none">
                    <span class="text-gray-500">{{ $order->city?->name ?? __('Онлайн') }}</span>
                    <span class="font-semibold text-gray-900">
                        @if ($order->budget_min)
                            {{ number_format($order->budget_min, 0, ',', ' ') }}
                            @if ($order->budget_max && $order->budget_max != $order->budget_min)
                                – {{ number_format($order->budget_max, 0, ',', ' ') }}
                            @endif
                            MDL
                        @else
                            {{ __('Договорная') }}
                        @endif
                    </span>
                </div>
                <div class="relative z-10 mt-2 text-xs text-gray-400 pointer-events-none">{{ $order->offers_count }} {{ __('откликов') }}</div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                {{ __('В избранном пока пусто.') }}
            </div>
        @endforelse
    </div>
</div>
