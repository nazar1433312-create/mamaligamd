<div x-data="{ tab: 'customer' }">
    <h1 class="text-2xl font-bold mb-6">Мои заказы</h1>

    <div class="flex gap-4 border-b border-gray-200 mb-6">
        <button @click="tab = 'customer'" :class="tab === 'customer' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500'"
            class="pb-3 border-b-2 text-sm font-medium">Я заказчик ({{ $asCustomer->count() }})</button>
        <button @click="tab = 'executor'" :class="tab === 'executor' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500'"
            class="pb-3 border-b-2 text-sm font-medium">Я исполнитель ({{ $asExecutor->count() }})</button>
    </div>

    <div x-show="tab === 'customer'" class="space-y-3">
        @forelse ($asCustomer as $order)
            <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-xs text-indigo-600">{{ $order->category->name }}</span>
                        <h3 class="font-medium">{{ $order->title }}</h3>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <div>{{ __(ucfirst($order->status)) }}</div>
                        <div>{{ $order->offers_count }} откликов</div>
                    </div>
                </div>
            </a>
        @empty
            <p class="text-gray-400 text-sm">Вы ещё не публиковали заказы.</p>
        @endforelse
    </div>

    <div x-show="tab === 'executor'" class="space-y-3" style="display: none;">
        @forelse ($asExecutor as $order)
            <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-xs text-indigo-600">{{ $order->category->name }}</span>
                        <h3 class="font-medium">{{ $order->title }}</h3>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <div>{{ __(ucfirst($order->status)) }}</div>
                        <div>Моя цена: {{ $order->offers->first()?->price }} грн</div>
                    </div>
                </div>
            </a>
        @empty
            <p class="text-gray-400 text-sm">Вы ещё не откликались на заказы.</p>
        @endforelse
    </div>
</div>
