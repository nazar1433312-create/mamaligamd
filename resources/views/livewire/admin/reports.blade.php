<div>
    <h1 class="text-2xl font-bold mb-6">Жалобы</h1>

    <div class="space-y-4">
        @forelse ($reports as $report)
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <div class="flex justify-between items-start mb-2">
                    <div class="text-sm">
                        <a href="{{ route('users.show', $report->reporter) }}" class="font-medium text-indigo-600 hover:underline">{{ $report->reporter->name }}</a>
                        <span class="text-gray-400">пожаловался</span>
                        @if ($report->reportedUser)
                            на
                            <a href="{{ route('users.show', $report->reportedUser) }}" class="font-medium text-indigo-600 hover:underline">{{ $report->reportedUser->name }}</a>
                        @endif
                        @if ($report->order)
                            · заказ
                            <a href="{{ route('orders.show', $report->order) }}" class="text-indigo-600 hover:underline">#{{ $report->order->id }} {{ $report->order->title }}</a>
                        @endif
                        <span class="text-xs text-gray-400 ms-2">{{ $report->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <span @class([
                        'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                        'bg-amber-100 text-amber-700' => $report->status === 'open',
                        'bg-blue-100 text-blue-700' => $report->status === 'reviewed',
                        'bg-gray-100 text-gray-700' => $report->status === 'closed',
                    ])>{{ $report->status }}</span>
                </div>

                <p class="text-sm text-gray-700 whitespace-pre-line mb-3">{{ $report->reason }}</p>

                @if ($report->status !== 'closed')
                    <div class="flex gap-2">
                        @if ($report->status === 'open')
                            <button wire:click="markReviewed({{ $report->id }})" class="text-sm bg-blue-600 text-white px-3 py-1.5 rounded-md hover:bg-blue-700">Взять в работу</button>
                        @endif
                        <button wire:click="close({{ $report->id }})" wire:confirm="Закрыть жалобу?" class="text-sm bg-gray-200 text-gray-700 px-3 py-1.5 rounded-md hover:bg-gray-300">Закрыть</button>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-400">Жалоб нет.</p>
        @endforelse
    </div>
</div>
