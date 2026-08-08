<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-2">{{ __('История переписки и звонков') }}</h1>

    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        @if ($user->history_paid_at)
            <p class="text-sm text-green-700 font-medium">
                ✅ {{ __('Ваша переписка и звонки хранятся навсегда.') }}
            </p>
        @else
            <p class="text-sm text-gray-600 mb-4">
                {{ __('По умолчанию личные сообщения и звонки старше 30 дней автоматически удаляются. Заплатите один раз, чтобы хранить их навсегда.') }}
            </p>
            <a href="{{ route('history.unlock') }}"
               class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                💳 {{ __('Оплатить') }} {{ $feeAmount }} MDL {{ __('и хранить навсегда') }}
            </a>
        @endif
    </div>

    <h2 class="text-lg font-bold mb-3">{{ __('Звонки') }}</h2>
    <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
        @forelse ($calls as $call)
            @php $other = $call->caller_id === $user->id ? $call->callee : $call->caller; @endphp
            <div class="flex items-center justify-between p-4">
                <div>
                    <p class="text-sm font-medium">{{ $other?->name }}</p>
                    <p class="text-xs text-gray-500">{{ $call->created_at->format('d.m.Y H:i') }}</p>
                </div>
                <span class="text-xs text-gray-500">
                    @if ($call->status === \App\Models\Call::STATUS_MISSED)
                        {{ __('Пропущен') }}
                    @elseif ($call->status === \App\Models\Call::STATUS_DECLINED)
                        {{ __('Отклонён') }}
                    @else
                        {{ __('Завершён') }}
                    @endif
                </span>
            </div>
        @empty
            <p class="p-4 text-sm text-gray-500">{{ __('Звонков пока нет.') }}</p>
        @endforelse
    </div>
</div>
