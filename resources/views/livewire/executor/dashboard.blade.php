<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">{{ __('Панель исполнителя') }}</h1>

        <div class="flex items-center bg-gray-100 rounded-md p-0.5 text-xs font-medium">
            @foreach (['day' => __('День'), 'week' => __('Неделя'), 'month' => __('Месяц'), 'year' => __('Год')] as $value => $label)
                <button wire:click="$set('period', '{{ $value }}')"
                    class="px-3 py-1.5 rounded {{ $period === $value ? 'bg-white shadow text-indigo-700' : 'text-gray-500' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-2xl font-bold">{{ $activeJobs->count() }}</div>
            <div class="text-sm text-gray-500">{{ __('Активные работы') }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-2xl font-bold">{{ $completedJobsCount }}</div>
            <div class="text-sm text-gray-500">{{ __('Завершено за период') }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-2xl font-bold">{{ number_format($totalEarned, 0, ',', ' ') }} MDL</div>
            <div class="text-sm text-gray-500">{{ __('Заработано за период') }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
            <div class="text-2xl font-bold">⭐ {{ number_format(auth()->user()->rating_avg, 1) }}</div>
            <div class="text-sm text-gray-500">{{ __('Рейтинг') }} ({{ auth()->user()->rating_count }})</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div>
            <h2 class="font-semibold mb-3">{{ __('Мои текущие работы') }}</h2>
            <div class="space-y-3">
                @forelse ($activeJobs as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400">
                        <div class="text-xs text-indigo-600">{{ $order->category->name }}</div>
                        <div class="font-medium">{{ $order->title }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ __('Заказчик') }}: {{ $order->customer->name }} · {{ $order->acceptedOffer->price }} MDL</div>
                    </a>
                @empty
                    <p class="text-sm text-gray-400">{{ __('Нет активных работ.') }}</p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="font-semibold mb-3">{{ __('Доступные заказы') }}</h2>
            <div class="space-y-3">
                @forelse ($availableOrders as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400">
                        <div class="text-xs text-indigo-600">{{ $order->category->name }}</div>
                        <div class="font-medium">{{ $order->title }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            📍 {{ $order->city?->name ?? __('Онлайн') }}
                            @if ($order->budget_min) · 💰 {{ number_format($order->budget_min, 0, ',', ' ') }} MDL @endif
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-400">{{ __('Пока нет доступных заказов в вашем режиме.') }}</p>
                @endforelse
            </div>
            <a href="{{ route('orders.index') }}" wire:navigate class="inline-block mt-3 text-sm text-indigo-600 hover:underline">{{ __('Все заказы') }} →</a>
        </div>
    </div>

    <div>
        <h2 class="font-semibold mb-3">{{ __('История выполненных работ') }} ({{ __('за период') }})</h2>
        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
            @forelse ($recentCompleted as $order)
                <a href="{{ route('orders.show', $order) }}" wire:navigate class="flex justify-between items-center p-3 hover:bg-gray-50">
                    <div>
                        <div class="text-xs text-indigo-600">{{ $order->category->name }}</div>
                        <div class="font-medium text-sm">{{ $order->title }}</div>
                    </div>
                    <div class="text-right text-sm text-gray-500">
                        <div>{{ $order->completed_at->format('d.m.Y') }}</div>
                        <div>{{ $order->acceptedOffer->price }} MDL</div>
                    </div>
                </a>
            @empty
                <p class="text-sm text-gray-400 p-3">{{ __('Нет завершённых работ за выбранный период.') }}</p>
            @endforelse
        </div>
        <div class="text-xs text-gray-400 mt-2">{{ __('Всего завершено за всё время') }}: {{ $completedTotalCount }}</div>
    </div>
</div>
