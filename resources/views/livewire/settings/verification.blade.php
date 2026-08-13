<div class="max-w-xl mx-auto bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
    <h1 class="text-xl font-bold mb-2">{{ __('Верификация') }}</h1>
    <p class="text-sm text-gray-500 mb-6">
        {{ __('Подтвердите личность, чтобы получить галочку "Проверено" и открыть оплату картой. Загрузите фото документа (паспорт/ID) — данные видит только администратор.') }}
    </p>

    @if (auth()->user()->is_verified)
        <div class="p-4 bg-green-50 text-green-700 rounded-md text-sm flex items-center gap-2">
            <x-verified-badge /> {{ __('Вы верифицированы.') }}
        </div>
    @elseif ($latest && $latest->status === 'pending')
        <div class="p-4 bg-amber-50 text-amber-700 rounded-md text-sm">
            {{ __('Заявка на рассмотрении. Обычно проверка занимает до 24 часов.') }}
        </div>
    @else
        @if ($latest && $latest->status === 'rejected')
            <div class="p-4 bg-red-50 text-red-700 rounded-md text-sm mb-4">
                {{ __('Предыдущая заявка отклонена.') }}
                @if ($latest->admin_comment) {{ $latest->admin_comment }} @endif
                {{ __('Попробуйте загрузить документы ещё раз.') }}
            </div>
        @endif

        <form wire:submit="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Фото документа (1–3 файла)') }}</label>
                <input type="file" wire:model="documents" multiple accept="image/*" class="w-full text-sm">
                <div wire:loading wire:target="documents" class="text-xs text-gray-400 mt-1">{{ __('Загрузка...') }}</div>
                @error('documents') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                @error('documents.*') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" class="w-full bg-indigo-600 text-white py-2.5 rounded-md font-medium hover:bg-indigo-700 disabled:opacity-50">
                {{ __('Отправить на проверку') }}
            </button>
        </form>
    @endif
</div>
