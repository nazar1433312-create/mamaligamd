<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">{{ __('Сообщения') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ __('Здесь личная переписка с другими пользователями сайта.') }}</p>

    <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
        @forelse ($conversations as $conv)
            <a href="{{ route('messages.show', $conv['user']) }}" wire:navigate class="flex items-center gap-3 p-4 hover:bg-gray-50">
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                    {{ mb_substr($conv['user']->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1 text-sm {{ $conv['unread'] > 0 ? 'font-bold' : 'font-medium' }}">
                        {{ $conv['user']->name }}
                        @if ($conv['user']->is_verified) <x-verified-badge /> @endif
                    </div>
                    <div class="text-sm truncate {{ $conv['unread'] > 0 ? 'text-gray-900 font-medium' : 'text-gray-500' }}">
                        {{ $conv['last_message']->body }}
                    </div>
                </div>
                <div class="flex flex-col items-end gap-1 shrink-0">
                    <span class="text-xs text-gray-400">{{ $conv['last_message']->created_at->diffForHumans() }}</span>
                    @if ($conv['unread'] > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-indigo-600 text-white text-xs font-bold">
                            {{ $conv['unread'] }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="p-8 text-center">
                <p class="text-sm text-gray-500 mb-3">{{ __('Сообщений пока нет.') }}</p>
                <p class="text-sm text-gray-400 mb-4">{{ __('Зайдите в профиль специалиста и нажмите «Написать», чтобы начать переписку.') }}</p>
                <a href="{{ route('users.directory') }}" wire:navigate class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                    {{ __('Специалисты') }}
                </a>
            </div>
        @endforelse
    </div>
</div>
