<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg border border-gray-200 flex flex-col h-[32rem]" wire:poll.5s>
        <div class="p-4 border-b border-gray-200">
            <div class="font-semibold">{{ __('Обращение') }} #{{ $ticket->id }}: {{ $ticket->subject }}</div>
            <div class="text-xs text-gray-500">{{ __('Статус') }}: {{ $ticket->status === 'open' ? __('Открыто') : __('Закрыто') }}</div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            @forelse ($messages as $msg)
                <div @class(['text-sm', 'text-right' => $msg->sender_type === 'user'])>
                    <div @class([
                        'inline-block px-3 py-2 rounded-lg max-w-[85%]',
                        'bg-indigo-600 text-white' => $msg->sender_type === 'user',
                        'bg-gray-100 text-gray-800' => $msg->sender_type === 'admin',
                    ])>
                        {{ $msg->body }}
                    </div>
                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $msg->sender_type === 'admin' ? __('Поддержка') : __('Вы') }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ __('Сообщений пока нет.') }}</p>
            @endforelse
        </div>

        <form wire:submit="reply" class="p-3 border-t border-gray-200 flex gap-2">
            <input type="text" wire:model="newMessage" placeholder="{{ __('Написать...') }}" class="flex-1 rounded-md border-gray-300 text-sm">
            <button type="submit" class="bg-indigo-600 text-white px-3 rounded-md text-sm">➤</button>
        </form>
    </div>
</div>
