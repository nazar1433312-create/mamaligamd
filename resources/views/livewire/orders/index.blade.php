<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">Заказы</h1>
        <a href="{{ route('orders.create') }}" wire:navigate class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
            + Опубликовать заказ
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6 bg-white p-4 rounded-lg border border-gray-200">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Поиск по заголовку..."
               class="rounded-md border-gray-300 text-sm sm:col-span-2">

        <select wire:model.live="category_id" class="rounded-md border-gray-300 text-sm">
            <option value="">Все категории</option>
            @foreach ($categories as $cat)
                <optgroup label="{{ $cat->name }}">
                    <option value="{{ $cat->id }}">{{ $cat->name }} (все)</option>
                    @foreach ($cat->children as $child)
                        <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>

        <select wire:model.live="city_id" class="rounded-md border-gray-300 text-sm">
            <option value="">Все города</option>
            @foreach ($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" wire:navigate
               class="block bg-white p-5 rounded-lg border border-gray-200 hover:border-indigo-400 hover:shadow-sm transition">
                <div class="text-xs text-indigo-600 font-medium mb-1">{{ $order->category->name }}</div>
                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $order->title }}</h3>
                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $order->description }}</p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">{{ $order->city?->name ?? 'Онлайн' }}</span>
                    <span class="font-semibold text-gray-900">
                        @if ($order->budget_min)
                            {{ number_format($order->budget_min, 0, ',', ' ') }}
                            @if ($order->budget_max && $order->budget_max != $order->budget_min)
                                – {{ number_format($order->budget_max, 0, ',', ' ') }}
                            @endif
                            грн
                        @else
                            Договорная
                        @endif
                    </span>
                </div>
                <div class="mt-2 text-xs text-gray-400">{{ $order->offers_count }} откликов</div>
            </a>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                Заказов не найдено.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
