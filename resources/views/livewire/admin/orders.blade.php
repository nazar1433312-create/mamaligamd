<div>
    <h1 class="text-2xl font-bold mb-6">Заказы</h1>

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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</div>
