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

    @if ($topExecutors->isNotEmpty())
        <div class="mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">🏆 {{ __('Топ исполнителей') }}</h2>
            <div class="flex flex-wrap gap-3">
                @foreach ($topExecutors as $executor)
                    <a href="{{ route('users.show', $executor) }}" wire:navigate
                       class="flex items-center gap-2 bg-gray-50 hover:bg-indigo-50 border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                            {{ mb_substr($executor->name, 0, 1) }}
                        </span>
                        <span class="font-medium text-gray-900">{{ $executor->name }}</span>
                        @if ($executor->is_verified) <x-verified-badge /> @endif
                        <x-rating-badge :user="$executor" />
                        <span class="text-xs text-gray-400">· {{ $executor->completed_jobs_count }} {{ __('заказов') }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

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
            <div class="relative bg-white p-5 rounded-xl border border-gray-200 hover:border-indigo-400 hover:shadow-md transition">
                <a href="{{ route('orders.show', $order) }}" wire:navigate class="absolute inset-0 z-0" aria-label="{{ $order->title }}"></a>

                <div class="relative z-10 flex items-start justify-between mb-1 pointer-events-none">
                    <span class="text-xs text-indigo-600 font-medium">{{ $order->category->name }}</span>
                    @auth
                        <div class="pointer-events-auto">
                            <livewire:orders.favorite-button :order="$order" :key="'fav-'.$order->id" />
                        </div>
                    @endauth
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
                {{ __('Заказов не найдено.') }}
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</div>
