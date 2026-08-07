<div>
    <h1 class="text-2xl font-bold mb-6">{{ __('Специалисты') }}</h1>

    <div class="flex flex-col sm:flex-row gap-4 mb-6 bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Поиск по имени...') }}"
               class="flex-1 rounded-md border-gray-300 text-sm">

        <select wire:model.live="sort" class="rounded-md border-gray-300 text-sm">
            <option value="rating">{{ __('По рейтингу') }}</option>
            <option value="completed">{{ __('По выполненным заказам') }}</option>
            <option value="oldest">{{ __('Дольше всех на платформе') }}</option>
            <option value="newest">{{ __('Новые участники') }}</option>
        </select>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($users as $user)
            <a href="{{ route('users.show', $user) }}" wire:navigate class="block bg-white p-5 rounded-xl border border-gray-200 hover:border-indigo-400 hover:shadow-md transition">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold flex items-center gap-1 truncate">
                            {{ $user->name }}
                            @if ($user->is_verified) <x-verified-badge /> @endif
                        </div>
                        <div class="text-xs text-gray-500">{{ $user->city?->name ?? __('Молдова') }}</div>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>⭐ {{ number_format($user->rating_avg, 1) }} ({{ $user->rating_count }})</span>
                    <span>✅ {{ $user->completed_jobs_count }} {{ __('заказов') }}</span>
                </div>
                <div class="text-xs text-gray-400 mt-2">{{ __('На платформе с') }} {{ $user->created_at->format('m.Y') }}</div>
            </a>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                {{ __('Никого не найдено.') }}
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
