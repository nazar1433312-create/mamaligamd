<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg border border-gray-200">
    <h1 class="text-xl font-bold mb-6">Новый заказ</h1>

    <form wire:submit="save" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Категория</label>
            <select wire:model="category_id" class="w-full rounded-md border-gray-300 text-sm">
                <option value="">Выберите категорию</option>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Заголовок</label>
            <input type="text" wire:model="title" class="w-full rounded-md border-gray-300 text-sm" placeholder="Например: Почистить квартиру 2 комнаты">
            @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
            <textarea wire:model="description" rows="5" class="w-full rounded-md border-gray-300 text-sm"
                placeholder="Опишите подробно, что нужно сделать"></textarea>
            @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Бюджет от</label>
                <input type="number" wire:model="budget_min" class="w-full rounded-md border-gray-300 text-sm">
                @error('budget_min') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Бюджет до</label>
                <input type="number" wire:model="budget_max" class="w-full rounded-md border-gray-300 text-sm">
                @error('budget_max') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Город</label>
            <select wire:model="city_id" class="w-full rounded-md border-gray-300 text-sm">
                <option value="">Не указан / онлайн</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Адрес (необязательно)</label>
            <input type="text" wire:model="address" class="w-full rounded-md border-gray-300 text-sm">
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-md font-medium hover:bg-indigo-700">
            Опубликовать заказ
        </button>
    </form>
</div>
