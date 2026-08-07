<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">{{ __('Сообщения') }}</h1>

    <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
        @forelse ($conversations as $conv)
            <a href="{{ route('messages.show', $conv['user']) }}" wire:navigate class="flex items-center gap-3 p-4 hover:bg-gray-50">
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                    {{ mb_substr($conv['user']->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1 font-medium text-sm">
                        {{ $conv['user']->name }}
                        @if ($conv['user']->is_verified) <x-verified-badge /> @endif
                    </div>
                    <div class="text-sm text-gray-500 truncate">{{ $conv['last_message']->body }}</div>
                </div>
                <div class="text-xs text-gray-400 shrink-0">{{ $conv['last_message']->created_at->diffForHumans() }}</div>
            </a>
        @empty
            <p class="text-sm text-gray-400 p-4">{{ __('Сообщений пока нет.') }}</p>
        @endforelse
    </div>
</div>
