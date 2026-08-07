<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ __('Мои обращения') }}</h1>
        <a href="{{ route('support.create') }}" wire:navigate class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            + {{ __('Новое обращение') }}
        </a>
    </div>

    <div class="space-y-3">
        @forelse ($tickets as $ticket)
            <a href="{{ route('support.show', $ticket) }}" wire:navigate class="block bg-white p-4 rounded-lg border border-gray-200 hover:border-indigo-400">
                <div class="flex justify-between items-center">
                    <span class="font-medium">{{ $ticket->subject }}</span>
                    <span class="text-xs {{ $ticket->status === 'open' ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $ticket->status === 'open' ? __('Открыто') : __('Закрыто') }}
                    </span>
                </div>
            </a>
        @empty
            <p class="text-sm text-gray-400">{{ __('У вас пока нет обращений в поддержку.') }}</p>
        @endforelse
    </div>
</div>
