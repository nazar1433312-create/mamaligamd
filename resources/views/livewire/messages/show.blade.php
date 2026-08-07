<div class="max-w-2xl mx-auto">
    <a href="{{ route('messages.inbox') }}" wire:navigate class="text-sm text-gray-500 hover:text-indigo-600">← {{ __('Сообщения') }}</a>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-[32rem] mt-3" wire:poll.5s>
        <div class="p-4 border-b border-gray-200 font-semibold text-sm flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('users.show', $otherUser) }}" wire:navigate class="hover:text-indigo-600">{{ $otherUser->name }}</a>
                @if ($otherUser->is_verified) <x-verified-badge /> @endif
            </div>
            <button type="button"
                onclick="MamaligaCall.start({{ $otherUser->id }}, '{{ addslashes($otherUser->name) }}')"
                title="{{ __('Позвонить') }}"
                class="text-indigo-600 hover:text-indigo-800">
                📞
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            @forelse ($messages as $msg)
                <div @class(['text-sm', 'text-right' => $msg->sender_id === auth()->id()])>
                    <div @class([
                        'inline-block px-3 py-2 rounded-lg max-w-[85%]',
                        'bg-indigo-600 text-white' => $msg->sender_id === auth()->id(),
                        'bg-gray-100 text-gray-800' => $msg->sender_id !== auth()->id(),
                    ])>
                        {{ $msg->body }}
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400">{{ __('Сообщений пока нет.') }}</p>
            @endforelse
        </div>
        <form wire:submit="send" class="p-3 border-t border-gray-200 flex gap-2">
            <input type="text" wire:model="newMessage" placeholder="{{ __('Написать...') }}" class="flex-1 rounded-md border-gray-300 text-sm">
            <button type="submit" class="bg-indigo-600 text-white px-3 rounded-md text-sm">➤</button>
        </form>
    </div>
</div>
