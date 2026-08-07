<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Заказы</h1>
        <button wire:click="deleteAll" wire:confirm="Удалить ВСЕ заказы без возможности восстановления? Это действие для очистки тестовых данных."
            class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-md hover:bg-red-700">
            🗑 Удалить все заказы
        </button>
    </div>

    <select wire:model.live="status" class="rounded-md border-gray-300 text-sm mb-4">
        <option value="">Все статусы</option>
        <option value="open">Open</option>
        <option value="in_progress">In progress</option>
        <option value="completed">Completed</option>
        <option value="disputed">Disputed</option>
        <option value="cancelled">Cancelled</option>
    </select>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Заголовок</th>
                    <th class="px-4 py-2">Заказчик</th>
                    <th class="px-4 py-2">Статус</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($orders as $order)
                    <tr>
                        <td class="px-4 py-2">{{ $order->id }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('orders.show', $order) }}" wire:navigate class="text-indigo-600 hover:underline">{{ $order->title }}</a>
                        </td>
                        <td class="px-4 py-2">{{ $order->customer->name }}</td>
                        <td class="px-4 py-2">{{ $order->status }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            @if ($order->status === 'disputed')
                                <button wire:click="resolveDispute({{ $order->id }}, 'complete')" class="text-xs text-green-600 hover:underline">Завершить</button>
                                <button wire:click="resolveDispute({{ $order->id }}, 'cancel')" class="text-xs text-red-600 hover:underline">Отменить</button>
                            @else
                                @if (in_array($order->status, ['open', 'in_progress']))
                                    <button wire:click="markDisputed({{ $order->id }})" class="text-xs text-orange-600 hover:underline">Спор</button>
                                    <button wire:click="forceCancel({{ $order->id }})" wire:confirm="Отменить заказ?" class="text-xs text-red-600 hover:underline">Отменить</button>
                                @endif
                            @endif
                            <button wire:click="deleteOrder({{ $order->id }})" wire:confirm="Удалить заказ #{{ $order->id }} без возможности восстановления?" class="text-xs text-red-600 hover:underline">Удалить</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
