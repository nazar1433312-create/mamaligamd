<div>
    <h1 class="text-2xl font-bold mb-6">Пользователи</h1>

    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Поиск по имени/email..."
        class="w-full max-w-sm rounded-md border-gray-300 text-sm mb-4">

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2">Имя</th>
                    <th class="px-4 py-2">Телефон / TG</th>
                    <th class="px-4 py-2">Рейтинг</th>
                    <th class="px-4 py-2">Статус</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-4 py-2">{{ $user->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $user->phone ?? '@'.$user->telegram_username }}</td>
                        <td class="px-4 py-2">{{ number_format($user->rating_avg, 1) }} ({{ $user->rating_count }})</td>
                        <td class="px-4 py-2">
                            @if ($user->is_banned)
                                <span class="text-red-600">Забанен</span>
                            @else
                                <span class="text-green-600">Активен</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            <button wire:click="toggleBan({{ $user->id }})" wire:confirm="Подтвердите действие"
                                wire:loading.attr="disabled"
                                class="text-xs text-indigo-600 hover:underline disabled:opacity-50">
                                {{ $user->is_banned ? 'Разбанить' : 'Забанить' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
