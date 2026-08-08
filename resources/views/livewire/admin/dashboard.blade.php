<div>
    <h1 class="text-2xl font-bold mb-6">Админ-панель</h1>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="text-2xl font-bold">{{ $usersCount }}</div>
            <div class="text-sm text-gray-500">Пользователей</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="text-2xl font-bold">{{ $ordersCount }}</div>
            <div class="text-sm text-gray-500">Заказов всего</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="text-2xl font-bold">{{ $openOrders }}</div>
            <div class="text-sm text-gray-500">Открытых заказов</div>
        </div>
        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <div class="text-2xl font-bold text-red-600">{{ $disputedOrders }}</div>
            <div class="text-sm text-gray-500">Споров</div>
        </div>
    </div>

    <div class="flex gap-4 text-sm font-medium">
        <a href="{{ route('admin.users') }}" wire:navigate class="text-indigo-600 hover:underline">Пользователи →</a>
        <a href="{{ route('admin.categories') }}" wire:navigate class="text-indigo-600 hover:underline">Категории →</a>
        <a href="{{ route('admin.orders') }}" wire:navigate class="text-indigo-600 hover:underline">Заказы →</a>
        <a href="{{ route('admin.support') }}" wire:navigate class="text-indigo-600 hover:underline">Поддержка →</a>
        <a href="{{ route('admin.verifications') }}" wire:navigate class="text-indigo-600 hover:underline">Верификации →</a>
        <a href="{{ route('admin.reports') }}" wire:navigate class="text-indigo-600 hover:underline">Жалобы →</a>
        <a href="{{ route('admin.payouts') }}" wire:navigate class="text-indigo-600 hover:underline">Выплаты →</a>
    </div>
</div>
