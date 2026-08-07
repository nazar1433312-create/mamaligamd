<div x-data="{ tab: 'customer' }">
    <h1 class="text-2xl font-bold mb-6">{{ __('Мои заказы') }}</h1>

    <div class="flex gap-4 border-b border-gray-200 mb-6">
        <button @click="tab = 'customer'" :class="tab === 'customer' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500'"
            class="pb-3 border-b-2 text-sm font-medium">{{ __('Я заказчик') }} ({{ $customerActive->count() + $customerHistory->count() }})</button>
        <button @click="tab = 'executor'" :class="tab === 'executor' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500'"
            class="pb-3 border-b-2 text-sm font-medium">{{ __('Я исполнитель') }} ({{ $executorActive->count() + $executorHistory->count() }})</button>
    </div>

    <div x-show="tab === 'customer'" class="space-y-8">
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Активные') }}</h2>
            <div class="space-y-3">
                @forelse ($customerActive as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-xs text-indigo-600">{{ $order->category->name }}</span>
                                <h3 class="font-medium">{{ $order->title }}</h3>
                                <span class="text-xs text-gray-400">{{ $order->created_at->format('d.m.Y') }}</span>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <div>{{ __(ucfirst($order->status)) }}</div>
                                <div>{{ $order->offers_count }} {{ __('откликов') }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-400 text-sm">{{ __('Нет активных заказов.') }}</p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('История') }}</h2>
            <div class="space-y-3">
                @forelse ($customerHistory as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400 opacity-80">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-xs text-indigo-600">{{ $order->category->name }}</span>
                                <h3 class="font-medium">{{ $order->title }}</h3>
                                <span class="text-xs text-gray-400">{{ $order->created_at->format('d.m.Y') }}</span>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <div>{{ __(ucfirst($order->status)) }}</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-400 text-sm">{{ __('Вы ещё не публиковали заказы.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div x-show="tab === 'executor'" class="space-y-8" style="display: none;">
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('Активные') }}</h2>
            <div class="space-y-3">
                @forelse ($executorActive as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-xs text-indigo-600">{{ $order->category->name }}</span>
                                <h3 class="font-medium">{{ $order->title }}</h3>
                                <span class="text-xs text-gray-400">{{ $order->created_at->format('d.m.Y') }}</span>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <div>{{ __(ucfirst($order->status)) }}</div>
                                <div>{{ __('Моя цена') }}: {{ $order->offers->first()?->price }} MDL</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-400 text-sm">{{ __('Нет активных работ.') }}</p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">{{ __('История') }}</h2>
            <div class="space-y-3">
                @forelse ($executorHistory as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400 opacity-80">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-xs text-indigo-600">{{ $order->category->name }}</span>
                                <h3 class="font-medium">{{ $order->title }}</h3>
                                <span class="text-xs text-gray-400">{{ $order->created_at->format('d.m.Y') }}</span>
                            </div>
                            <div class="text-right text-sm text-gray-500">
                                <div>{{ __(ucfirst($order->status)) }}</div>
                                <div>{{ __('Моя цена') }}: {{ $order->offers->first()?->price }} MDL</div>
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-400 text-sm">{{ __('Вы ещё не откликались на заказы.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
