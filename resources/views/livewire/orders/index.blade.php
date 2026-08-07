<div>
    <div class="mb-8 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 bg-gradient-to-br from-indigo-600 to-indigo-800 text-white">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl sm:text-4xl font-extrabold mb-2">{{ __('Найдите исполнителя за минуты') }}</h1>
            <p class="text-indigo-100 mb-6 max-w-xl">{{ __('Услуги мастеров и специалистов по всей Молдове — размещайте заказы бесплатно и выбирайте лучшее предложение.') }}</p>
            <a href="{{ route('orders.create') }}" wire:navigate
               class="inline-block bg-amber-400 text-indigo-900 font-semibold px-5 py-2.5 rounded-lg hover:bg-amber-300 transition">
                + {{ __('Опубликовать заказ') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Поиск по заголовку...') }}"
               class="rounded-md border-gray-300 text-sm sm:col-span-2">

        <select wire:model.live="category_id" class="rounded-md border-gray-300 text-sm">
            <option value="">{{ __('Все категории') }}</option>
            @foreach ($categories as $cat)
                <optgroup label="{{ $cat->name }}">
                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ __('все') }})</option>
                    @foreach ($cat->children as $child)
                        <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>

        <select wire:model.live="city_id" class="rounded-md border-gray-300 text-sm">
            <option value="">{{ __('Все города') }}</option>
            @foreach ($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" wire:navigate
               class="block bg-white p-5 rounded-xl border border-gray-200 hover:border-indigo-400 hover:shadow-md transition">
                <div class="text-xs text-indigo-600 font-medium mb-1">{{ $order->category->name }}</div>
                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ $order->title }}</h3>
                <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $order->description }}</p>
                <div class="flex items-center justify-between text-sm">
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
                <div class="mt-2 text-xs text-gray-400">{{ $order->offers_count }} {{ __('откликов') }}</div>
            </a>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                {{ __('Заказов не найдено.') }}
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
