<div>
    <h1 class="text-2xl font-bold mb-2">Выплаты исполнителям</h1>
    <p class="text-sm text-gray-500 mb-6">
        Victoriabank не даёт API для отправки денег на карту — переведи вручную через свой интернет-банкинг
        и отметь как выплачено.
    </p>

    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Ожидают выплаты ({{ $pending->count() }})</h2>
    <div class="space-y-3 mb-8">
        @forelse ($pending as $payment)
            @php
                $executor = $payment->order?->acceptedOffer?->executor;
                $card = $executor?->payoutMethods->firstWhere('is_default', true) ?? $executor?->payoutMethods->first();
            @endphp
            <div class="bg-white p-4 rounded-lg border border-gray-200 flex items-center justify-between">
                <div>
                    <div class="font-medium">
                        {{ $executor?->name ?? '—' }}
                        <span class="text-gray-400 font-normal">·</span>
                        <a href="{{ route('orders.show', $payment->order) }}" class="text-indigo-600 hover:underline">
                            #{{ $payment->order?->id }} {{ $payment->order?->title }}
                        </a>
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Сумма: <b>{{ number_format($payment->amount, 2) }} MDL</b>
                        @if ($card)
                            · Карта: <span class="font-mono">{{ $card->card_number_encrypted }}</span> ({{ $card->card_masked }})
                        @else
                            · <span class="text-red-600">нет карты для выплаты на файле</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400 mt-1">Создано: {{ $payment->created_at->format('d.m.Y H:i') }}</div>
                </div>
                <button wire:click="markPaid({{ $payment->id }})" wire:confirm="Подтверди, что деньги уже реально переведены исполнителю."
                    class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-md hover:bg-green-700 shrink-0 ms-4">
                    ✅ Отметить как выплачено
                </button>
            </div>
        @empty
            <p class="text-sm text-gray-400">Нет выплат в ожидании.</p>
        @endforelse
    </div>

    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Недавно выплачено</h2>
    <div class="space-y-2">
        @forelse ($paid as $payment)
            <div class="bg-white p-3 rounded-lg border border-gray-200 text-sm flex items-center justify-between">
                <div>
                    {{ $payment->order?->acceptedOffer?->executor?->name ?? '—' }} —
                    #{{ $payment->order?->id }} {{ $payment->order?->title }}
                </div>
                <div class="text-gray-500">{{ number_format($payment->amount, 2) }} MDL</div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Пусто.</p>
        @endforelse
    </div>
</div>
