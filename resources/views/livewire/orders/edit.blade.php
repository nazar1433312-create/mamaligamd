<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
    <h1 class="text-xl font-bold mb-6">{{ __('Редактировать заказ') }}</h1>

    <form wire:submit="save" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Категория') }}</label>
            <select wire:model="category_id" class="w-full rounded-md border-gray-300 text-sm">
                <option value="">{{ __('Выберите категорию') }}</option>
                @foreach ($categories as $cat)
                    <optgroup label="{{ $cat->name }}">
                        @foreach ($cat->children as $child)
                            <option value="{{ $child->id }}">{{ $child->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('category_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Заголовок') }}</label>
            <input type="text" wire:model="title" class="w-full rounded-md border-gray-300 text-sm" placeholder="{{ __('Например: Почистить квартиру 2 комнаты') }}">
            @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Описание') }}</label>
            <textarea wire:model="description" rows="5" class="w-full rounded-md border-gray-300 text-sm"
                placeholder="{{ __('Опишите подробно, что нужно сделать') }}"></textarea>
            @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Бюджет от') }} (MDL)</label>
                <input type="number" wire:model="budget_min" class="w-full rounded-md border-gray-300 text-sm">
                @error('budget_min') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Бюджет до') }} (MDL)</label>
                <input type="number" wire:model="budget_max" class="w-full rounded-md border-gray-300 text-sm">
                @error('budget_max') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Город') }}</label>
            <select wire:model="city_id" class="w-full rounded-md border-gray-300 text-sm">
                <option value="">{{ __('Не указан / онлайн') }}</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Адрес (необязательно)') }}</label>
            <input type="text" wire:model="address" class="w-full rounded-md border-gray-300 text-sm">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="flex-1 bg-indigo-600 text-white py-2.5 rounded-md font-medium hover:bg-indigo-700">
                {{ __('Сохранить изменения') }}
            </button>
            <a href="{{ route('orders.show', $order) }}" wire:navigate
                class="flex-1 text-center bg-gray-100 text-gray-700 py-2.5 rounded-md font-medium hover:bg-gray-200">
                {{ __('Отмена') }}
            </a>
        </div>
    </form>
</div>
