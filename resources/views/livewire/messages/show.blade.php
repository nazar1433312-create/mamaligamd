<div class="max-w-2xl mx-auto">
    <a href="{{ route('messages.inbox') }}" wire:navigate class="text-sm text-gray-500 hover:text-indigo-600">← {{ __('Сообщения') }}</a>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-[32rem] mt-3" wire:poll.5s>
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <a href="{{ route('users.show', $otherUser) }}" wire:navigate class="flex items-center gap-2 hover:text-indigo-600">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ mb_substr($otherUser->name, 0, 1) }}
                </div>
                <span class="font-semibold text-sm flex items-center gap-1">
                    {{ $otherUser->name }}
                    @if ($otherUser->is_verified) <x-verified-badge /> @endif
                </span>
            </a>
            <button type="button"
                onclick="MamaligaCall.start({{ $otherUser->id }}, '{{ addslashes($otherUser->name) }}')"
                class="flex items-center gap-1.5 text-sm text-indigo-600 hover:text-white hover:bg-indigo-600 border border-indigo-200 hover:border-indigo-600 rounded-full px-3 py-1.5 transition">
                📞 <span class="hidden sm:inline">{{ __('Позвонить') }}</span>
            </button>
        </div>

        <div
            class="flex-1 overflow-y-auto p-4 space-y-3"
            x-data
            x-init="
                const scrollToBottom = () => { $el.scrollTop = $el.scrollHeight };
                scrollToBottom();
                new MutationObserver(scrollToBottom).observe($el, { childList: true, subtree: true });
            "
        >
            @forelse ($messages as $msg)
                <div @class(['text-sm', 'text-right' => $msg->sender_id === auth()->id()])>
                    <div @class([
                        'inline-block px-3 py-2 rounded-lg max-w-[85%] text-left',
                        'bg-indigo-600 text-white' => $msg->sender_id === auth()->id(),
                        'bg-gray-100 text-gray-800' => $msg->sender_id !== auth()->id(),
                    ])>
                        {{ $msg->body }}
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center text-gray-400">
                    <p class="text-sm mb-1">{{ __('Сообщений пока нет.') }}</p>
                    <p class="text-xs">{{ __('Напишите первым — ниже есть поле для сообщения.') }}</p>
                </div>
            @endforelse
        </div>

        <form wire:submit="send" class="p-3 border-t border-gray-200 flex gap-2">
            <input type="text" wire:model="newMessage" placeholder="{{ __('Написать...') }}" autocomplete="off"
                class="flex-1 rounded-md border-gray-300 text-sm">
            <button type="submit" class="bg-indigo-600 text-white px-4 rounded-md text-sm font-medium hover:bg-indigo-700">
                {{ __('Отправить') }}
            </button>
        </form>
        @error('newMessage') <p class="text-red-600 text-xs px-3 pb-2">{{ $message }}</p> @enderror
    </div>
</div>
