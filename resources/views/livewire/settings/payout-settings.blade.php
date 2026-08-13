<div class="max-w-md mx-auto bg-white p-6 rounded-lg border border-gray-200">
    <h1 class="text-xl font-bold mb-4">Карта для получения выплат</h1>

    @if (session('status'))
        <p class="text-sm text-green-700 mb-4">{{ session('status') }}</p>
    @endif

    @if ($current)
        <p class="text-sm text-gray-600 mb-4">Текущая карта: <b>{{ $current->card_masked }}</b></p>
    @else
        <p class="text-sm text-gray-500 mb-4">Карта ещё не привязана. Она понадобится, чтобы получать оплату за выполненные заказы.</p>
    @endif

    <form wire:submit="save" class="space-y-3">
        <input type="text" wire:model="cardNumber" placeholder="Номер карты" maxlength="19"
            class="w-full rounded-md border-gray-300 text-sm">
        @error('cardNumber') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

        <button type="submit" wire:loading.attr="disabled" class="w-full bg-indigo-600 text-white py-2 rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
            Сохранить
        </button>
    </form>
</div>
