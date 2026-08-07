<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <h1 class="text-xl font-bold mb-4">Обращения</h1>
        <div class="space-y-2">
            @foreach ($tickets as $ticket)
                <button wire:click="open({{ $ticket->id }})"
                    class="w-full text-left p-3 rounded-md border {{ $openTicketId === $ticket->id ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 bg-white' }}">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium">#{{ $ticket->id }} {{ $ticket->subject }}</span>
                        <span class="{{ $ticket->status === 'open' ? 'text-green-600' : 'text-gray-400' }}">{{ $ticket->status }}</span>
                    </div>
                    <div class="text-xs text-gray-500">{{ $ticket->contactLabel() }}</div>
                </button>
            @endforeach
        </div>
    </div>

    <div class="lg:col-span-2">
        @if ($openTicket)
            <div class="bg-white rounded-lg border border-gray-200 flex flex-col h-[32rem]">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <div class="font-semibold">#{{ $openTicket->id }} {{ $openTicket->subject }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $openTicket->contactLabel() }}
                            @if ($openTicket->guest_contact) ({{ $openTicket->guest_contact }}) @endif
                        </div>
                    </div>
                    @if ($openTicket->status === 'open')
                        <button wire:click="close({{ $openTicket->id }})" class="text-xs text-red-600 hover:underline">Закрыть</button>
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    @foreach ($openTicket->messages as $msg)
                        <div @class(['text-sm', 'text-right' => $msg->sender_type === 'admin'])>
                            <div @class([
                                'inline-block px-3 py-2 rounded-lg max-w-[85%]',
                                'bg-indigo-600 text-white' => $msg->sender_type === 'admin',
                                'bg-gray-100 text-gray-800' => $msg->sender_type === 'user',
                            ])>
                                {{ $msg->body }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <form wire:submit="sendReply" class="p-3 border-t border-gray-200 flex gap-2">
                    <input type="text" wire:model="reply" placeholder="Ответить..." class="flex-1 rounded-md border-gray-300 text-sm">
                    <button type="submit" class="bg-indigo-600 text-white px-3 rounded-md text-sm">➤</button>
                </form>
            </div>
        @else
            <p class="text-sm text-gray-400">Выберите обращение слева.</p>
        @endif
    </div>
</div>
